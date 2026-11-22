@extends('layouts.admin')

@section('title')
    Control Center
@endsection

@section('content-header')
    <h1>Control Center <small>Manage your game hosting infrastructure</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">SNCK</a></li>
        <li class="active">Control Center</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <section class="snck-welcome">
            <div class="snck-welcome-copy">
                <span class="snck-eyebrow"><i class="fa fa-bolt"></i> SNCK CONTROL CENTER</span>
                <h2>Your infrastructure.<br>One control plane.</h2>
                <p>Manage nodes, game servers, users and service templates from one place.</p>
                <div class="snck-welcome-actions">
                    <a class="btn btn-primary" href="{{ route('admin.nodes') }}"><i class="fa fa-cubes"></i> Manage nodes</a>
                    <a class="btn btn-default" href="{{ route('admin.servers') }}"><i class="fa fa-server"></i> View servers</a>
                </div>
            </div>
            <div class="snck-welcome-mark" aria-hidden="true"><span>SNCK</span><i class="fa fa-bolt"></i></div>
        </section>
    </div>
</div>

<div class="row snck-section-heading">
    <div class="col-xs-12">
        <h3>Workspace</h3>
        <p>Shortcuts to the tools you use to operate your hosting platform.</p>
    </div>
</div>

<div class="row snck-workspace-grid">
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.nodes') }}">
            <span class="snck-card-icon purple"><i class="fa fa-cubes"></i></span>
            <span class="snck-card-copy"><strong>Nodes</strong><small>Manage compute locations, node configuration and allocations.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.servers') }}">
            <span class="snck-card-icon cyan"><i class="fa fa-server"></i></span>
            <span class="snck-card-copy"><strong>Game servers</strong><small>Create, inspect and manage hosted game instances.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.users') }}">
            <span class="snck-card-icon blue"><i class="fa fa-users"></i></span>
            <span class="snck-card-copy"><strong>Users</strong><small>Manage accounts, access and administrator roles.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.databases') }}">
            <span class="snck-card-icon green"><i class="fa fa-database"></i></span>
            <span class="snck-card-copy"><strong>Databases</strong><small>Configure database hosts available to game servers.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.nests') }}">
            <span class="snck-card-icon orange"><i class="fa fa-cubes"></i></span>
            <span class="snck-card-copy"><strong>Nests &amp; Eggs</strong><small>Manage game templates, images and startup definitions.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-4">
        <a class="snck-workspace-card" href="{{ route('admin.settings') }}">
            <span class="snck-card-icon pink"><i class="fa fa-sliders"></i></span>
            <span class="snck-card-copy"><strong>Panel settings</strong><small>Configure the panel and its global behavior.</small></span>
            <i class="fa fa-arrow-right snck-card-arrow"></i>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="snck-release-note">
            <div><i class="fa fa-shield"></i></div>
            <section>
                <strong>SNCK Panel · Built for game hosting</strong>
                <p>SNCK keeps the established panel API and node workflows while adding its own interface and operator experience. Server runtime actions require a correctly configured and connected Wings node.</p>
            </section>
            <a href="https://github.com/SnckBoy/snck-panel" target="_blank" rel="noopener noreferrer">Project <i class="fa fa-external-link"></i></a>
        </div>
    </div>
</div>
@endsection
