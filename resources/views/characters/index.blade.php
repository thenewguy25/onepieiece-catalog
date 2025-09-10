@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <form method="POST" action="{{ route('characters.search') }}" class="mb-3">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="Search characters by name..."
                            value="{{ $searchQuery }}" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if($searchQuery)
                            <a href="{{ route('characters.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </div>
                </form>

                @if($searchQuery)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Search results for: <strong>"{{ $searchQuery }}"</strong>
                        @if(count($characters) === 0)
                            - No characters found
                        @else
                            - {{ count($characters) }} character(s) found
                        @endif
                    </div>
                @else
                    <div class="alert alert-primary">
                        <i class="fas fa-users"></i>
                        Showing the Straw Hat Pirates crew members
                    </div>
                @endif
            </div>
        </div>

        <!-- Characters Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($characters as $character)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $character['image'] }}" class="card-img-top" alt="{{ $character['name'] }}"
                            style="height: 300px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $character['name'] }}</h5>
                            <p class="card-text">
                                <strong>Bounty:</strong> {{ $character['bounty'] }} Berries<br>
                                @if($character['devil_fruit'])
                                    <strong>Devil Fruit:</strong> {{ $character['devil_fruit'] }}
                                @else
                                    <strong>Devil Fruit:</strong> None
                                @endif
                            </p>
                            <a href="{{ route('characters.show', $character['id']) }}" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-focus on search input when page loads
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.querySelector('input[name="name"]');
                if (searchInput) {
                    searchInput.focus();
                }
            });
        </script>
    @endpush
@endsection