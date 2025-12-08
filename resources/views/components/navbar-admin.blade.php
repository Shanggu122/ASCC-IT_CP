@php($current = request()->path())
<!-- Hamburger button for mobile -->
<button class="hamburger" id="hamburger">&#9776;</button>
<div class="sidebar" id="sidebar">
        <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Logo">
        <!-- Simple role indicator -->
        <div class="simple-role-indicator">
                <span class="role-line admin-line"></span>
                <span class="role-label">Admin Portal</span>
        </div>
        <ul>
            <li>
                <a class="nav-link {{ str_starts_with($current,'admin-dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class='bx bx-home nav-icon' aria-hidden="true"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a class="nav-link {{ str_contains($current,'admin-comsci') ? 'active' : '' }}" href="{{ url('/admin-comsci') }}">
                    <x-icons.comsci aria-hidden="true" />
                    <span class="nav-label">Computer Science</span>
                </a>
            </li>
            <li>
                <a class="nav-link {{ str_contains($current,'admin-itis') ? 'active' : '' }}" href="{{ url('/admin-itis') }}">
                    <x-icons.itis aria-hidden="true" />
                    <span class="nav-label">IT &amp; IS</span>
                </a>
            </li>
            <li>
                <a class="nav-link {{ str_contains($current,'admin-analytics') ? 'active' : '' }}" href="{{ url('/admin-analytics') }}">
                    <i class='bx bx-bar-chart nav-icon' aria-hidden="true"></i>
                    <span class="nav-label">Analytics</span>
                </a>
            </li>
            <li>
                <x-logout-link guard="admin" label="Sign Out" class="nav-link nav-logout" icon="bx-log-out" />
            </li>
        </ul>
</div>
<script>
    (function(){
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        if(hamburger){
            hamburger.addEventListener('click',()=>{
                sidebar.classList.toggle('active');
                hamburger.classList.toggle('active');
            });
        }
    })();
</script>