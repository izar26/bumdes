@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-ban"></i> {{ session('error') }}
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-exclamation-triangle"></i> {{ session('warning') }}
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-info-circle"></i> {{ session('info') }}
</div>
@endif
