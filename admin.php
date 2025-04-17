<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library Admin Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      margin-top: 40px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">LibrarySystem</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Books</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Members</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Admin</a></li>
        </ul>
        <form class="d-flex" role="search">
          <input id="searchInput" class="form-control me-2" type="search" placeholder="Search records..." aria-label="Search">
        </form>
      </div>
    </div>
  </nav>

  <!-- Table Placeholder -->
  <div class="container table-container">
    <h3 class="mb-4">Library Records</h3>
    <table class="table table-striped table-hover" id="recordsTable">
      <thead class="table-dark">
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
