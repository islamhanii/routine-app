<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- Notify -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Custom JS -->
<script>
     window.config = {
        csrf: '{{ csrf_token() }}',
        routes: {
            darkMode: '{{ route('dark.mode') }}',
        },
        styles: {
            darkMode: '{{ asset("assets/css/dark-mode.css") }}?v={{ filemtime(public_path('assets/css/dark-mode.css')) }}',
        }
     };
</script>
<script src="{{ URL::asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>
@yield('js')
