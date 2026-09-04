<div class="card mt-4">
    <div class="card-body">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="/admin">Bosh sahifa</a></li>
              @foreach ($datas as $data)
                <li class="breadcrumb-item {{ $data['active'] ? 'active' : '' }}" {{ $data['active'] ? 'aria-current="page"' : '' }}>
                    @if($data['disabled'])
                        {{ $data['name'] }}
                    @else
                        @if($data['active'])
                            {{ $data['name'] }}
                        @else
                            <a href="{{ $data['url'] }}">{{ $data['name'] }}</a>
                        @endif
                    @endif
                </li>
              @endforeach
            </ol>
        </nav>
    </div>
</div>
