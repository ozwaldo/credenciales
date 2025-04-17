<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Credencial Digital') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-blue-950 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center">

                    {{-- Foto de Perfil --}}
                    <div class="mb-4">
                        @if ($user->ruta_foto_perfil)
                            <img src="{{ asset('storage/' . $user->ruta_foto_perfil) }}" alt="Foto de perfil" class="w-32 h-32 rounded-lg object-cover border-4 border-indigo-300">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center border-4 border-indigo-300">
                                <svg class="w-16 h-16 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Nombre de Usuario --}}
                    <h1 class="text-2xl font-bold mb-4"> {{ $user->name }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }} </h1>

                    {{-- Datos generales del Usuario --}}

                    {{-- Datos específicos del Rol --}}
                    @if ($user->hasRole('estudiante') && $user->estudiante)
                        <div class="text-center mb-4 space-y-1">
                            <p><span class="font-semibold">{{ $user->estudiante->numero_control }}</span>  </p>
                            <p>
                                <span class="font-semibold">
                                    {{
                                        match ($user->estudiante->carrera) {
                                            'isa' => 'Ingeniería en Sistemas Automotrices',
                                            'ii' => 'Ingeniería Industrial',
                                            'isc' => 'Ingeniería en Sistemas Computacionales',
                                            'ige' => 'Ingeniería en Gestión Empresarial',
                                            'ie' => 'Ingeniería Electrónica',
                                            'gas' => 'Gastronomía',
                                            'ia' => 'Ingeniería Ambiental',
                                            'is' => 'Ingeniería en Semiconductores',
                                            default => $user->estudiante->carrera ?? 'No especificada'
                                        }
                                    }}
                                </span>
                            </p>
                            <p class="mt-2 px-3 py-1 inline-block bg-blue-100 text-blue-800 text-sm font-semibold rounded-xl dark:bg-white dark:text-blue-950">
                                Estudiante
                            </p>
                        </div>
                    @elseif ($user->hasRole('visitante') && $user->visitante)
                        <div class="text-center mb-4 space-y-1">
                            <p> <span class="font-semibold">Código Visitante: </span> {{ $user->visitante->codigo_visitante }} </p>
                            <p> <span class="font-semibold">Institución: </span> {{ $user->visitante->institucion_origen }} </p>
                            <p class="mt-2 px-3 py-1 inline-block bg-green-100 text-green-800 text-sm font-semibold rounded-full dark:bg-green-900 dark:text-green-300">
                                Visitante
                            </p>
                        </div>
                    @endif

                    {{-- Código QR --}}
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6 w-52 flex justify-center">
                        <div id="qr-code-container" class="relative w-64 h-64">

                            {{-- Placeholder mientras carga o si ocurre un error --}}
                            <div id="qr-loading" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- Imagen del Código QR --}}

                            <img id="qr-code-image" src="" alt="Código QR" class="w-52 h-52 object-contain rounded-lg" style="display: none;">

                        </div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">El código se actualiza periódicamente</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para aztualizar el QR --}}
    @push('scripts')
    <script>

        const qrImg = document.getElementById('qr-code-image'); // Imagen del QR
        const qrLoading = document.getElementById('qr-loading'); // Placeholder de carga
        const qrCodeContainer = document.getElementById('qr-code-container'); // Contenedor del QR

        const refreshInterval = 300000; // 3 min

        function loadQrCode() {
            qrLoading.style.display = 'flex'; // Mostrar el placeholder de carga
            qrImg.style.display = 'none'; // Ocultar la imagen del QR

            const qrUrl = `{{ route('credential.qr.code') }}?t=${new Date().getTime()}`; // URL del QR

            const img = new Image();
            img.onload = () => {
                qrImg.src = img.src; // asigna el src de la imagen cargada al src del QR
                qrImg.style.display = 'block'; // Mostrar la imagen del QR
                qrLoading.style.display = 'none'; // Ocultar el placeholder de carga
            };

            img.onerror = () => {
                qrLoading.style.display = 'flex';
                qrImg.style.display = 'none'; // Ocultar la imagen del QR
                qrCodeContainer.innerHTML = '<p class="text-red-500">Error al cargar el código QR</p>'; // Mensaje de error

            };

            img.src = qrUrl; // Asignar la URL del QR a la imagen
         }

         loadQrCode();

         setInterval(loadQrCode, refreshInterval); // Actualizar el QR cada 30 segundos

    </script>
    @endpush
</x-app-layout>
