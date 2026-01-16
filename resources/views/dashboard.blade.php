<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Customs CRM Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <strong>Welcome, {{ auth()->user()->name }}!</strong>
                        <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full ml-2
                            @if(auth()->user()->hasRole('admin')) bg-red-500
                            @elseif(auth()->user()->hasRole('inspector')) bg-green-500
                            @elseif(auth()->user()->hasRole('analyst')) bg-blue-500
                            @elseif(auth()->user()->hasRole('broker')) bg-yellow-500 text-black
                            @endif">
                            {{ ucfirst(auth()->user()->getRoleNames()->first()) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                        @can('view vehicles')
                            <a href="/vehicles" class="block p-4 bg-blue-100 hover:bg-blue-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ \App\Models\Vehicle::count() }}</div>
                                <div class="text-sm text-blue-800">Vehicles</div>
                            </a>
                        @endcan

                        @can('view parties')
                            <a href="/parties" class="block p-4 bg-green-100 hover:bg-green-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ \App\Models\Party::count() }}</div>
                                <div class="text-sm text-green-800">Parties</div>
                            </a>
                        @endcan

                        @can('view cases')
                            <a href="/cases" class="block p-4 bg-purple-100 hover:bg-purple-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ \App\Models\CaseModel::count() }}</div>
                                <div class="text-sm text-purple-800">Cases</div>
                            </a>
                        @endcan

                        @can('view inspections')
                            <a href="/inspections" class="block p-4 bg-orange-100 hover:bg-orange-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ \App\Models\Inspection::count() }}</div>
                                <div class="text-sm text-orange-800">Inspections</div>
                            </a>
                        @endcan

                        @can('view documents')
                            <a href="/documents" class="block p-4 bg-red-100 hover:bg-red-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">{{ \App\Models\Document::count() }}</div>
                                <div class="text-sm text-red-800">Documents</div>
                            </a>
                        @endcan

                        @can('manage users')
                            <a href="/admin/users" class="block p-4 bg-gray-100 hover:bg-gray-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-gray-600">{{ \App\Models\User::count() }}</div>
                                <div class="text-sm text-gray-800">Users</div>
                            </a>
                        @endcan
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Your Permissions:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach(auth()->user()->getAllPermissions() as $permission)
                                <span class="inline-block px-2 py-1 text-xs bg-gray-200 rounded">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    @can('manage users')
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Panel</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="{{ route('admin.users.index') }}" class="block p-4 bg-red-100 hover:bg-red-200 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">{{ \App\Models\User::count() }}</div>
                                <div class="text-sm text-red-800">Manage Users</div>
                            </a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
