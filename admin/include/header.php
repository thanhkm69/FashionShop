<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">
  <div class="container-fluid justify-content-between">

    <!-- Left: Icon + Admin Title -->
    <div class="d-flex align-items-center gap-2">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <i class="bi bi-gear-fill fs-3" style="color: #dc3545;"></i>
        <span class="fw-bold" style="color: #dc3545;">Panel</span>
      </a>
    </div>


    <!-- Right: Notification + User Info -->
    <div class="d-flex align-items-center">

      <!-- Notification -->
      <div class="notification-section me-3 position-relative">
        <a href="#" class="text-dark position-relative d-inline-block" style="line-height: 1;">
          <i class="bi bi-bell-fill fs-5"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            1
          </span>
        </a>
      </div>


      <!-- User Info -->
      <button class="d-flex align-items-center border-0 bg-transparent p-0">
        <img src="<?= $dir.$nguoiDung["hinh"] ?>" alt="Avatar" width="40" height="40" class="rounded-circle border border-dark me-2">
        <span class="fw-medium text-dark"><?= $nguoiDung["ten"] ?></span>
      </button>

    </div>
  </div>
</nav>