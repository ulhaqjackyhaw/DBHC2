@extends('layouts.app')

@section('title', 'Import Data Karyawan')
@section('header-title', 'Import Data Karyawan')

@push('head-styles')
    <style>
        .progress-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 2rem;
        }

        .progress {
            height: 30px;
            border-radius: 15px;
            overflow: visible;
            background-color: #e9ecef;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            border-radius: 15px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: width 0.3s ease;
            background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
        }

        .status-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .spinner-border {
            width: 60px;
            height: 60px;
            border-width: 6px;
        }

        .message-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .success-box {
            border-left-color: #198754;
            background: #d1e7dd;
        }

        .error-box {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
    </style>
@endpush

@section('content')
    <div class="progress-container">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div id="statusIcon" class="status-icon">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <h3 class="mb-4" id="statusTitle">Memproses Import Data...</h3>

                <div class="progress mb-3">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                        style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        0%
                    </div>
                </div>

                <div id="messageBox" class="message-box">
                    <p class="mb-0" id="statusMessage">Menginisialisasi proses import...</p>
                </div>

                <div id="actionButtons" class="mt-4" style="display: none;">
                    <a href="{{ route('karyawan.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Data Karyawan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('body-scripts')
    <script>
        const sessionId = "{{ $sessionId }}";
        let checkInterval;

        function updateProgress() {
            fetch(`/data-karyawan/import-progress/${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const progress = data.progress || 0;
                    const status = data.status || 'processing';
                    const message = data.message || 'Memproses...';

                    // Update progress bar
                    const progressBar = document.getElementById('progressBar');
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressBar.textContent = Math.round(progress) + '%';

                    // Update message
                    document.getElementById('statusMessage').textContent = message;

                    // Handle completion
                    if (status === 'completed') {
                        clearInterval(checkInterval);

                        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                        progressBar.classList.add('bg-success');

                        document.getElementById('statusIcon').innerHTML =
                            '<i class="bi bi-check-circle-fill text-success"></i>';
                        document.getElementById('statusTitle').textContent = 'Import Berhasil!';

                        const messageBox = document.getElementById('messageBox');
                        messageBox.classList.add('success-box');

                        document.getElementById('actionButtons').style.display = 'block';

                        // Auto redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = "{{ route('karyawan.index') }}";
                        }, 3000);
                    }
                    // Handle error
                    else if (status === 'error') {
                        clearInterval(checkInterval);

                        progressBar.classList.remove('progress-bar-animated');
                        progressBar.classList.add('bg-danger');

                        document.getElementById('statusIcon').innerHTML =
                            '<i class="bi bi-x-circle-fill text-danger"></i>';
                        document.getElementById('statusTitle').textContent = 'Import Gagal';

                        const messageBox = document.getElementById('messageBox');
                        messageBox.classList.add('error-box');

                        document.getElementById('actionButtons').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error checking progress:', error);
                });
        }

        // Start checking progress every 500ms
        checkInterval = setInterval(updateProgress, 500);
        updateProgress(); // Initial check
    </script>
@endpush
