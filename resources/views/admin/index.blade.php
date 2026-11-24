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
<div class="snck-page-intro">
    <div>
        <h2>Infrastructure</h2>
        <p>Operate nodes, game servers, accounts and templates from one place.</p>
    </div>
    <div class="snck-page-actions">
        <a class="btn btn-primary" href="{{ route('admin.nodes') }}"><i class="fa fa-cubes" aria-hidden="true"></i> Manage nodes</a>
        <a class="btn btn-default" href="{{ route('admin.servers') }}"><i class="fa fa-server" aria-hidden="true"></i> View servers</a>
    </div>
</div>

<div class="row snck-section-heading">
    <div class="col-xs-12">
        <h3>Workspace</h3>
        <p>Direct access to the panel's administration tools.</p>
    </div>
</div>

<nav class="snck-tool-list" aria-label="Administration tools">
    <a class="snck-tool-row" href="{{ route('admin.nodes') }}">
        <span class="snck-tool-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Nodes</strong><small>Compute locations, node configuration and allocations</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
    <a class="snck-tool-row" href="{{ route('admin.servers') }}">
        <span class="snck-tool-icon"><i class="fa fa-server" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Game servers</strong><small>Create, inspect and manage hosted instances</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
    <a class="snck-tool-row" href="{{ route('admin.users') }}">
        <span class="snck-tool-icon"><i class="fa fa-users" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Users</strong><small>Accounts, access and administrator roles</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
    <a class="snck-tool-row" href="{{ route('admin.databases') }}">
        <span class="snck-tool-icon"><i class="fa fa-database" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Databases</strong><small>Database hosts available to game servers</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
    <a class="snck-tool-row" href="{{ route('admin.nests') }}">
        <span class="snck-tool-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Nests &amp; Eggs</strong><small>Game templates, images and startup definitions</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
    <a class="snck-tool-row" href="{{ route('admin.settings') }}">
        <span class="snck-tool-icon"><i class="fa fa-sliders" aria-hidden="true"></i></span>
        <span class="snck-tool-copy"><strong>Panel settings</strong><small>Global configuration and panel behavior</small></span>
        <i class="fa fa-angle-right snck-tool-arrow" aria-hidden="true"></i>
    </a>
</nav>

<div class="snck-release-note">
    <div><i class="fa fa-shield" aria-hidden="true"></i></div>
    <section>
        <strong>SNCK Panel · Game server control plane</strong>
        <p>Server runtime actions require a correctly configured and connected Wings node.</p>
    </section>
    <a href="https://github.com/SnckBoy/snck-panel" target="_blank" rel="noopener noreferrer">Repository <i class="fa fa-external-link" aria-hidden="true"></i></a>
</div>
@endsection
