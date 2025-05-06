<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | UST Public Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #e3f2fd;
    }

    .navbar {
      background-color: #0d47a1;
    }

    .navbar-brand, .nav-link {
      color: #fff !important;
    }

    .navbar-brand:hover, .nav-link:hover {
      color: #90caf9 !important;
    }

    .logo {
      height: 60px;
      margin-right: 15px;
    }

    .table-container {
      padding: 60px 0;
    }

    .table-container h3 {
      color: #0d47a1;
      margin-bottom: 30px;
      text-align: center;
    }

    .table thead {
      background-color: #0d47a1;
      color: #fff;
    }

    footer {
      background-color: #0d47a1;
      color: #fff;
      padding: 15px 0;
      text-align: center;
    }

    .form-control {
      max-width: 300px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a href="#" class="navbar-brand">
      <img src="images/logo.png" alt="Library Logo" class="logo">
      UST Public Library
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="homepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#products">Catalog</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Admin Table Section -->
<div class="container table-container">
  <h3>Library Records Management</h3>
  <div class="d-flex justify-content-end mb-3">
    <input id="searchInput" class="form-control" type="search" placeholder="Search records..." aria-label="Search">
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover" id="recordsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>001</td>
          <td>The Great Gatsby</td>
          <td>F. Scott Fitzgerald</td>
          <td>Fiction</td>
          <td>Available</td>
        </tr>
        <tr>
          <td>002</td>
          <td>1984</td>
          <td>George Orwell</td>
          <td>Dystopian</td>
          <td>Checked Out</td>
        </tr>
        <tr>
          <td>003</td>
          <td>To Kill a Mockingbird</td>
          <td>Harper Lee</td>
          <td>Classic</td>
          <td>Available</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<footer>
  <p>&copy; 2025 University of Santo Tomas Public Library. All rights reserved.</p>
</footer>

<script>
  const searchInput = document.getElementById("searchInput");
  const table = document.getElementById("recordsTable").getElementsByTagName("tbody")[0];

  searchInput.addEventListener("keyup", function () {
    const filter = searchInput.value.toLowerCase();
    const rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName("td");
      let found = false;

      for (let j = 0; j < cells.length; j++) {
        if (cells[j].innerText.toLowerCase().includes(filter)) {
          found = true;
          break;
        }
      }

      rows[i].style.display = found ? "" : "none";
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
