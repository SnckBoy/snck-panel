<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\SftpUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class SftpAuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nodeId' => ['required', 'string', 'max:64'],
            'username' => ['required', 'string', 'max:128'],
            'password' => ['required', 'string', 'max:512'],
        ]);
        $node = Node::query()->find($data['nodeId']);
        $token = str_starts_with((string) $request->header('Authorization'), 'Bearer ')
            ? substr((string) $request->header('Authorization'), 7) : '';
        if (! $node || $node->disabled || ! $this->matchesNodeCredential($node, $token)) {
            return response()->json(['error' => 'Unauthorized node.'], 401);
        }
        $account = SftpUser::query()->where('username', $data['username'])->first();
        if (! $account || (string) $account->server?->getAttribute('node_id') !== (string) $node->id || ! Hash::check($data['password'], (string) $account->password_hash)) {
            return response()->json(['error' => 'Invalid SFTP credentials.'], 401);
        }

        return response()->json(['authenticated' => true, 'serverId' => $account->server_id]);
    }

    private function matchesNodeCredential(Node $node, string $token): bool
    {
        if ($token === '' || strlen($token) > 512) {
            return false;
        }
        $hash = (string) $node->credential_hash;
        if ($hash !== '') {
            return hash_equals($hash, hash('sha256', $token));
        }
        if (! $node->credential_encrypted) {
            return false;
        }
        try {
            $expected = Crypt::decryptString($node->credential_encrypted);
        } catch (Throwable) {
            return false;
        }
        if (! hash_equals($expected, $token)) {
            return false;
        }
        DB::table('nodes')->where('id', $node->id)->whereNull('credential_hash')->update(['credential_hash' => hash('sha256', $token), 'updated_at' => now()]);

        return true;
    }
}
