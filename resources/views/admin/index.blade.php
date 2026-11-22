@extends('layouts.admin')

@section('title')
    Control Center
@endsection

@section('content-header')
    <h1>Control Center <small>Infrastructure overview</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">SNCK</a></li>
        <li class="active">Control Center</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <section class="snck-workspace-intro">
            <div class="snck-workspace-intro-copy">
                <span class="snck-eyebrow">SNCK / ADMINISTRATION</span>
                <p>Operational access to the services and configuration that keep your hosting platform running.</p>
            </div>
            <div class="snck-workspace-actions">
                <a class="btn btn-primary" href="{{ route('admin.nodes') }}"><i class="fa fa-cubes"></i> Manage nodes</a>
                <a class="btn btn-default" href="{{ route('admin.servers') }}"><i class="fa fa-server"></i> View servers</a>
            </div>
        </section>
    </div>
</div>

<div class="row snck-section-heading">
    <div class="col-xs-12">
        <h3>Workspace</h3>
        <p>Direct access to the main administration areas.</p>
    </div>
</div>

<div class="row snck-workspace-grid">
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.nodes') }}">
            <span class="snck-card-icon"><i class="fa fa-cubes"></i></span>
            <span class="snck-card-copy"><strong>Nodes</strong><small>Compute locations, node configuration and allocations.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.servers') }}">
            <span class="snck-card-icon"><i class="fa fa-server"></i></span>
            <span class="snck-card-copy"><strong>Game servers</strong><small>Create, inspect and manage hosted game instances.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.users') }}">
            <span class="snck-card-icon"><i class="fa fa-users"></i></span>
            <span class="snck-card-copy"><strong>Users</strong><small>Accounts, access and administrator roles.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.databases') }}">
            <span class="snck-card-icon"><i class="fa fa-database"></i></span>
            <span class="snck-card-copy"><strong>Databases</strong><small>Database hosts available to game servers.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.nests') }}">
            <span class="snck-card-icon"><i class="fa fa-cubes"></i></span>
            <span class="snck-card-copy"><strong>Nests &amp; Eggs</strong><small>Game templates, Docker images and startup definitions.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.settings') }}">
            <span class="snck-card-icon"><i class="fa fa-sliders"></i></span>
            <span class="snck-card-copy"><strong>Panel settings</strong><small>Global configuration and panel behavior.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow" aria-hidden="true"></i>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="snck-release-note">
            <div><i class="fa fa-info" aria-hidden="true"></i></div>
            <section>
                <strong>SNCK Panel / Game server control plane</strong>
                <p>Panel administration and node runtime are separate services. Server console, file and power operations require a correctly configured and connected Wings node.</p>
            </section>
            <a href="https://github.com/SnckBoy/snck-panel" target="_blank" rel="noopener noreferrer">Repository <i class="fa fa-external-link" aria-hidden="true"></i></a>
        </div>
    </div>
</div>
@endsection
