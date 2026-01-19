
@canany(['training_type_list'])
    <li class="nav-item  {{ request()->routeIs('admin.training-types.*') || request()->routeIs('admin.trainers.*') || request()->routeIs('admin.training.*')  ? 'active' : '' }}    ">
        <a class="nav-link"   data-href="#" data-bs-toggle="collapse" href="#trainingManagement" role="button" aria-expanded="false" aria-controls="shiftManagment">
            <i class="link-icon" data-feather="book"></i>
            <span class="link-title"> {{ __('index.training_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.training-types.*') || request()->routeIs('admin.trainers.*') || request()->routeIs('admin.training.*') ?'' : 'collapse'  }} " id="trainingManagement">
             <ul class="nav sub-menu">

                 @can('training_type_list')
                    <li class="nav-item">
                        <a href="{{route('admin.training-types.index')}}"
                           data-href="{{route('admin.training-types.index')}}"
                           class="nav-link {{request()->routeIs('admin.training-types.*') ? 'active' : ''}}">{{ __('index.training_type') }} </a>
                    </li>
                 @endcan
                 @can('list_trainer')
                     <li class="nav-item">
                         <a href="{{route('admin.trainers.index')}}"
                            data-href="{{route('admin.trainers.index')}}"
                            class="nav-link {{request()->routeIs('admin.trainers.*') ? 'active' : ''}}">{{ __('index.trainer') }} </a>
                     </li>
                 @endcan

                 @can('list_training')
                     <li class="nav-item">
                         <a href="{{route('admin.training.index')}}"
                            data-href="{{route('admin.training.index')}}"
                            class="nav-link {{request()->routeIs('admin.training.*') ? 'active' : ''}}">{{ __('index.training') }} </a>
                     </li>
                 @endcan

             </ul>
        </div>
    </li>
@endcanany
