<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Location;
use Illuminate\Http\Response;
use Pterodactyl\Models\Allocation;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Nodes\NodeUpdateService;
use Illuminate\Cache\Repository as CacheRepository;
use Pterodactyl\Services\Nodes\NodeCreationService;
use Pterodactyl\Services\Nodes\NodeDeletionService;
use Pterodactyl\Services\Allocations\AssignmentService;
use Pterodactyl\Services\Helpers\SoftwareVersionService;
use Pterodactyl\Http\Requests\Admin\Node\NodeFormRequest;
use Pterodactyl\Contracts\Repository\NodeRepositoryInterface;
use Pterodactyl\Contracts\Repository\ServerRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Node\AllocationFormRequest;
use Pterodactyl\Services\Allocations\AllocationDeletionService;
use Pterodactyl\Contracts\Repository\LocationRepositoryInterface;
use Pterodactyl\Contracts\Repository\AllocationRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Node\AllocationAliasFormRequest;

class NodesController extends Controller
{
    /**
     * NodesController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected AllocationDeletionService $allocationDeletionService,
        protected AllocationRepositoryInterface $allocationRepository,
        protected AssignmentService $assignmentService,
        protected CacheRepository $cache,
        protected NodeCreationService $creationService,
        protected NodeDeletionService $deletionService,
        protected LocationRepositoryInterface $locationRepository,
        protected NodeRepositoryInterface $repository,
        protected ServerRepositoryInterface $serverRepository,
        protected NodeUpdateService $updateService,
        protected SoftwareVersionService $versionService,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Displays create new node page.
     */
    public function create(): View|RedirectResponse
    {
        $locations = $this->locationRepository->all();
        if (count($locations) < 1) {
            $this->alert->warning(trans('admin/node.notices.location_required'))->flash();

            return redirect()->route('admin.locations');
        }

        return view('admin.nodes.new', ['locations' => $locations]);
    }

    /**
     * Post controller to create a new node on the system.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    public function store(NodeFormRequest $request): RedirectResponse
    {
        $node = $this->creationService->handle($request->normalize());
        $this->alert->info(trans('admin/node.notices.node_created'))->flash();

        return redirect()->route('admin.nodes.view.allocation', $node->id);
    }

    /**
     * Create and bootstrap a Wings node on this Panel host.
     */
    public function createLocalNode(Request $request): RedirectResponse
    {
        $host = $request->getHost();
        $scheme = $request->getScheme();

        $existing = Node::query()->where('fqdn', $host)->where('daemonListen', 8080)->first();
        if ($existing) {
            $this->alert->warning('A node already exists for this host on port 8080. Reusing the existing node; no duplicate was created.')->flash();

            return redirect()->route('admin.nodes.view', $existing->id);
        }

        if ($scheme === 'https') {
            $certificate = '/etc/letsencrypt/live/' . strtolower($host);
            if (!is_file($certificate . '/fullchain.pem') || !is_file($certificate . '/privkey.pem')) {
                $this->alert->danger('HTTPS is active, but the matching Let’s Encrypt certificate is missing. Install a valid certificate for this hostname before creating the local node.')->flash();

                return redirect()->route('admin.nodes');
            }
        }

        $helper = '/usr/local/sbin/snck-local-node-bootstrap';
        if (!is_executable($helper) || !is_file('/etc/sudoers.d/snck-local-node')) {
            $this->alert->danger('Local node setup helper is not installed. Run the SNCK Panel updater once, then try again.')->flash();

            return redirect()->route('admin.nodes');
        }

        $location = Location::query()->first();
        if (!$location) {
            $location = Location::query()->create([
                'short' => 'local',
                'long' => 'Local node on the Panel host',
            ]);
        }

        $memory = 1024;
        $meminfo = @file_get_contents('/proc/meminfo');
        if (is_string($meminfo) && preg_match('/^MemTotal:\\s+(\\d+)/m', $meminfo, $matches)) {
            $memory = max(1024, (int) floor(((int) $matches[1]) / 1024));
        }

        $diskBytes = @disk_total_space('/var/lib/pterodactyl') ?: @disk_total_space('/');
        $disk = max(1024, (int) floor(((float) $diskBytes) / 1048576));

        $node = $this->creationService->handle([
            'name' => 'Local Node',
            'description' => 'Automatically provisioned on the Panel host.',
            'location_id' => $location->id,
            'public' => true,
            'fqdn' => $host,
            'scheme' => $scheme,
            'behind_proxy' => false,
            'memory' => $memory,
            'memory_overallocate' => 0,
            'disk' => $disk,
            'disk_overallocate' => 0,
            'daemonBase' => '/var/lib/pterodactyl/volumes',
            'daemonSFTP' => 2022,
            'daemonListen' => 8080,
            'maintenance_mode' => false,
            'upload_size' => 100,
        ]);

        $process = proc_open(
            ['/usr/bin/sudo', '-n', $helper],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            $node->delete();
            $this->alert->danger('Could not start the local Wings installer. No node was retained.')->flash();

            return redirect()->route('admin.nodes');
        }

        fwrite($pipes[0], $node->id . PHP_EOL . $node->getYamlConfiguration());
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            $node->delete();
            logger()->error('Local Wings bootstrap could not be queued.', ['node_id' => $node->id, 'error' => trim((string) $errors)]);
            $this->alert->danger('Local node setup could not be started. Check sudo permissions and the SNCK local-node setup log. No node was retained.')->flash();

            return redirect()->route('admin.nodes');
        }

        $this->alert->success('Local node setup started. Wings is installing in the background; the node will show online after its API responds. Allow TCP 8080 and SFTP 2022 through your VPS firewall and cloud security group.')->flash();

        return redirect()->route('admin.nodes.view', $node->id);
    }

    /**
     * Updates settings for a node.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function updateSettings(NodeFormRequest $request, Node $node): RedirectResponse
    {
        $this->updateService->handle($node, $request->normalize(), $request->input('reset_secret') === 'on');
        $this->alert->success(trans('admin/node.notices.node_updated'))->flash();

        return redirect()->route('admin.nodes.view.settings', $node->id)->withInput();
    }

    /**
     * Removes a single allocation from a node.
     *
     * @throws \Pterodactyl\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveSingle(int $node, Allocation $allocation): Response
    {
        $this->allocationDeletionService->handle($allocation);

        return response('', 204);
    }

    /**
     * Removes multiple individual allocations from a node.
     *
     * @throws \Pterodactyl\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveMultiple(Request $request, int $node): Response
    {
        $allocations = $request->input('allocations');
        foreach ($allocations as $rawAllocation) {
            $allocation = new Allocation();
            $allocation->id = $rawAllocation['id'];
            $this->allocationRemoveSingle($node, $allocation);
        }

        return response('', 204);
    }

    /**
     * Remove all allocations for a specific IP at once on a node.
     */
    public function allocationRemoveBlock(Request $request, int $node): RedirectResponse
    {
        $this->allocationRepository->deleteWhere([
            ['node_id', '=', $node],
            ['server_id', '=', null],
            ['ip', '=', $request->input('ip')],
        ]);

        $this->alert->success(trans('admin/node.notices.unallocated_deleted', ['ip' => htmlspecialchars($request->input('ip'))]))
            ->flash();

        return redirect()->route('admin.nodes.view.allocation', $node);
    }

    /**
     * Sets an alias for a specific allocation on a node.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function allocationSetAlias(AllocationAliasFormRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $this->allocationRepository->update($request->input('allocation_id'), [
            'ip_alias' => (empty($request->input('alias'))) ? null : $request->input('alias'),
        ]);

        return response('', 204);
    }

    /**
     * Creates new allocations on a node.
     *
     * @throws \Pterodactyl\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \Pterodactyl\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \Pterodactyl\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \Pterodactyl\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function createAllocation(AllocationFormRequest $request, Node $node): RedirectResponse
    {
        $this->assignmentService->handle($node, $request->normalize());
        $this->alert->success(trans('admin/node.notices.allocations_added'))->flash();

        return redirect()->route('admin.nodes.view.allocation', $node->id);
    }

    /**
     * Deletes a node from the system.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function delete(int|Node $node): RedirectResponse
    {
        $this->deletionService->handle($node);
        $this->alert->success(trans('admin/node.notices.node_deleted'))->flash();

        return redirect()->route('admin.nodes');
    }
}
