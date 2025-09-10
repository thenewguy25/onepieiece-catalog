@extends('layouts.app')

@section('content')
    <div class="container py-4">
        @if(isset($character))
            <div class="row">
                <!-- Character Image -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <img src="{{ $character['image'] }}" class="card-img-top" alt="{{ $character['name'] }}"
                            style="height: 400px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h4 class="card-title">{{ $character['name'] }}</h4>
                            <p class="text-muted">ID: {{ $character['id'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Character Details -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Character Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary">Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $character['name'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bounty:</strong></td>
                                            <td>
                                                @if($character['bounty'] && $character['bounty'] !== 'Unknown')
                                                    <span class="badge bg-success">{{ $character['bounty'] }} Berries</span>
                                                @else
                                                    <span class="badge bg-secondary">Unknown</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Devil Fruit:</strong></td>
                                            <td>
                                                @if($character['devil_fruit'])
                                                    <span class="badge bg-danger">{{ $character['devil_fruit'] }}</span>
                                                @else
                                                    <span class="badge bg-info">None</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Source:</strong></td>
                                            <td>
                                                @if($character['source'] === 'one_piece_api')
                                                    <span class="badge bg-primary">One Piece API</span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($character['source']) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="text-primary">Crew Information</h5>
                                    @if($character['crew'])
                                        @if(is_array($character['crew']))
                                            @foreach($character['crew'] as $crewInfo)
                                                @if(is_array($crewInfo))
                                                    <div class="mb-3">
                                                        @if(isset($crewInfo['name']))
                                                            <strong>Crew:</strong> {{ $crewInfo['name'] }}<br>
                                                        @endif
                                                        @if(isset($crewInfo['description']))
                                                            <small class="text-muted">{{ $crewInfo['description'] }}</small><br>
                                                        @endif
                                                        @if(isset($crewInfo['status']))
                                                            <span class="badge bg-info">{{ $crewInfo['status'] }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p><strong>Crew:</strong> {{ $crewInfo }}</p>
                                                @endif
                                            @endforeach
                                        @else
                                            <p><strong>Crew:</strong> {{ $character['crew'] }}</p>
                                        @endif
                                    @else
                                        <p class="text-muted">No crew information available</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Details -->
                            @if(isset($character['job']) || isset($character['age']) || isset($character['size']) || isset($character['birthday']))
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="text-primary">Additional Details</h5>
                                        <div class="row">
                                            @if(isset($character['job']))
                                                <div class="col-md-3 mb-2">
                                                    <strong>Job:</strong><br>
                                                    <span class="badge bg-secondary">{{ $character['job'] }}</span>
                                                </div>
                                            @endif
                                            @if(isset($character['age']))
                                                <div class="col-md-3 mb-2">
                                                    <strong>Age:</strong><br>
                                                    <span class="badge bg-info">{{ $character['age'] }}</span>
                                                </div>
                                            @endif
                                            @if(isset($character['size']))
                                                <div class="col-md-3 mb-2">
                                                    <strong>Size:</strong><br>
                                                    <span class="badge bg-warning">{{ $character['size'] }}</span>
                                                </div>
                                            @endif
                                            @if(isset($character['birthday']))
                                                <div class="col-md-3 mb-2">
                                                    <strong>Birthday:</strong><br>
                                                    <span class="badge bg-success">{{ $character['birthday'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-3">
                        <a href="{{ route('characters.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Characters
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-danger">
                <h4>Character Not Found</h4>
                <p>The requested character could not be found.</p>
                <a href="{{ route('characters.index') }}" class="btn btn-primary">Back to Characters</a>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .card {
                border-radius: 15px;
                overflow: hidden;
            }

            .card-header {
                border-radius: 15px 15px 0 0 !important;
            }

            .badge {
                font-size: 0.9em;
            }

            .table td {
                padding: 0.5rem 0;
                vertical-align: middle;
            }
        </style>
    @endpush
@endsection