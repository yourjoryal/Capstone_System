<?php
/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "bsit_portal");
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

/* ADD EVENT */
if (isset($_POST['add_event'])) {

  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);

  /* IMAGE UPLOAD */
  $image_name = $_FILES['image']['name'];
  $image_tmp  = $_FILES['image']['tmp_name'];

  // Rename image to avoid duplicates
  $new_image = time() . "_" . $image_name;
  $upload_path = "public/images/" . $new_image;

  if (move_uploaded_file($image_tmp, $upload_path)) {

    $insert = "INSERT INTO events (title, event_date, description, image)
               VALUES ('$title', '$event_date', '$description', '$new_image')";
    mysqli_query($conn, $insert);

    header("Location: events.php?success=1");
    exit();
  } else {
    echo "Image upload failed.";
  }
}

/* DELETE EVENT */
if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  mysqli_query($conn, "DELETE FROM events WHERE id=$id");

  header("Location: events.php");
  exit();
}

/* FETCH EVENTS */
$result = mysqli_query($conn, "SELECT * FROM events ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events</title>

<style>
body {
  font-family: Arial, sans-serif;
  background: #0a1a33;
  color: #fff;
  margin: 0;
}

/* HEADER */
header {
  background: #08162b;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo img {
  width: 40px;
}
.hamburger {
  font-size: 24px;
  cursor: pointer;
}

/* SIDE MENU */
.side-menu {
  position: fixed;
  top: 0;
  left: -250px;
  width: 250px;
  height: 100%;
  background: #08162b;
  padding-top: 80px;
  transition: 0.3s;
}
.side-menu.open {
  left: 0;
}
.side-menu a {
  display: block;
  color: #fff;
  padding: 15px 20px;
  text-decoration: none;
}
.side-menu a:hover {
  background: #102654;
}

/* ADMIN FORM */
.admin-form {
  background: #fff;
  color: #000;
  padding: 20px;
  max-width: 600px;
  margin: 120px auto 30px;
  border-radius: 8px;
}
.admin-form input,
.admin-form textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
}
.admin-form button {
  background: #0a1a33;
  color: #fff;
  padding: 10px;
  border: none;
  cursor: pointer;
  width: 100%;
}
.success {
  color: green;
  text-align: center;
}

/* EVENTS */
.announcements-container {
  max-width: 1000px;
  margin: auto;
  padding: 20px;
}
.announcement-card {
  background: #fff;
  color: #000;
  display: flex;
  gap: 15px;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 15px;
}
.thumb img {
  width: 200px;
  height: 130px;
  object-fit: cover;
  border-radius: 6px;
}
.delete-btn {
  background: crimson;
  color: #fff;
  padding: 6px 12px;
  border-radius: 4px;
  text-decoration: none;
  display: inline-block;
  margin-top: 10px;
}

/* FOOTER */
footer {
  text-align: center;
  padding: 15px;
  background: #08162b;
  margin-top: 30px;
}
</style>
</head>

<body>

<header>
  <div class="logo">
    <img src="public/images/bsitlogo.jpg">
    <span>BSIT Department</span>
  </div>
  <div class="hamburger">☰</div>
</header>

<div id="sideMenu" class="side-menu">
  <a href="homepage.html">Home</a>
  <a href="faculty.html">Faculty</a>
  <a href="organizations.html">Student Organizations</a>
  <a href="announcements.html">Announcements</a>
  <a href="events.php">Events</a>
  <a href="achievements.html">Achievements</a>
  <a href="inquiries.html">Inquiries</a>
</div>

<!-- ADD EVENT -->
<div class="admin-form">
  <h2>Add New Event</h2>

  <?php if (isset($_GET['success'])) { ?>
    <p class="success">Event added successfully!</p>
  <?php } ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Event Title" required>
    <input type="text" name="event_date" placeholder="Event Date" required>
    <textarea name="description" placeholder="Event Description" required></textarea>
    <input type="file" name="image" accept="image/*" required>
    <button type="submit" name="add_event">Add Event</button>
  </form>
</div>

<!-- EVENTS LIST -->
<main class="announcements-container">

<?php if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) { ?>
    <div class="announcement-card">
      <div class="thumb">
        <img src="public/images/<?php echo $row['image']; ?>">
      </div>
      <div class="content">
        <h2><?php echo $row['title']; ?></h2>
        <p><?php echo $row['event_date']; ?></p>
        <p><?php echo $row['description']; ?></p>

        <a href="events.php?delete=<?php echo $row['id']; ?>"
           class="delete-btn"
           onclick="return confirm('Delete this event?');">
           Delete
        </a>
      </div>
    </div>
<?php } } else { ?>
  <p style="text-align:center;">No events available.</p>
<?php } ?>

</main>

<footer>
© 2025 BSIT Department - Cebu Technological University Tabuelan Campus
</footer>

<script>
const sideMenu = document.getElementById("sideMenu");
const hamburger = document.querySelector(".hamburger");

hamburger.onclick = () => sideMenu.classList.toggle("open");

document.onclick = (e) => {
  if (!sideMenu.contains(e.target) && !hamburger.contains(e.target)) {
    sideMenu.classList.remove("open");
  }
};
</script>

</body>
</html>
