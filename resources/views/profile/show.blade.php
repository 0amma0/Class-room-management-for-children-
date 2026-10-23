@if($user->isAdmin())
    @include('profile.admin')
@else
    @include('profile.guru')
@endif
