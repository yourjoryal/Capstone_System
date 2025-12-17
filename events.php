<?php
/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "bsit_portal");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* INITIALIZE EDIT VARIABLES */
$edit_mode = false;
$edit_id = 0;
$edit_title = '';
$edit_date = '';
$edit_description = '';
$edit_image = '';

/* FETCH EVENT FOR EDIT */
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_mode = true;

    $edit_result = mysqli_query($conn, "SELECT * FROM events WHERE id=$edit_id");
    if ($edit_row = mysqli_fetch_assoc($edit_result)) {
        $edit_title = $edit_row['title'];
        $edit_date = $edit_row['event_date'];
        $edit_description = $edit_row['description'];
        $edit_image = $edit_row['image'];
    }
}

/* ADD EVENT */
if (isset($_POST['add_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];

        $new_image = time() . "_" . $image_name;
        $upload_path = "public/images/" . $new_image;

        if (move_uploaded_file($image_tmp, $upload_path)) {
            $insert = "INSERT INTO events (title, event_date, description, image)
                       VALUES ('$title', '$event_date', '$description', '$new_image')";
            mysqli_query($conn, $insert);
            header("Location: events.php?success=1");
            exit();
        } else {
            $error = "❌ Image upload failed.";
        }
    } else {
        $error = "❌ No image selected.";
    }
}

/* UPDATE EVENT */
if (isset($_POST['update_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_sql = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];
        $new_image = time() . "_" . $image_name;
        $upload_path = "public/images/" . $new_image;

        if (move_uploaded_file($image_tmp, $upload_path)) {
            $image_sql = ", image='$new_image'";
        }
    }

    $update = "UPDATE events SET title='$title', event_date='$event_date', description='$description' $image_sql WHERE id=$edit_id";
    mysqli_query($conn, $update);

    header("Location: events.php?success=2");
    exit();
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
<link rel="stylesheet" href="public/css/announcements.css">
<style>
/* --- Your admin-form styles remain the same --- */
</style>
</head>
<body>

<!-- HEADER -->
<header>
  <div class="logo">
    <img src="public/images/bsitlogo.jpg" alt="BSIT Logo">
    <span>BSIT Department</span>
  </div>
  <div class="hamburger">☰</div>
</header>

<!-- SIDE MENU -->
<div id="sideMenu" class="side-menu">
  <a href="homepage.html">Home</a>
  <a href="faculty.html">Faculty</a>
  <a href="organizations.html">Student Organizations</a>
  <a href="announcements.html">Announcements</a>
  <a href="events.php">Events</a>
  <a href="achievements.html">Achievements</a>
  <a href="inquiries.html">Inquiries</a>
</div>

<!-- ADD / EDIT EVENT FORM -->
<div class="admin-form">
  <h2><?php echo $edit_mode ? 'Edit Event' : 'Add New Event'; ?></h2>

  <?php if (!empty($error)) echo "<p class='success'>$error</p>"; ?>
  <?php if (isset($_GET['success'])) {
      $msg = $_GET['success'] == 1 ? "Event added successfully!" : "Event updated successfully!";
      echo "<p class='success'>$msg</p>";
  } ?>

  <form method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Event Title" required value="<?php echo htmlspecialchars($edit_title); ?>">
      <input type="text" name="event_date" placeholder="Event Date (e.g. Jan 31, 2025 · 6:00 PM)" required value="<?php echo htmlspecialchars($edit_date); ?>">
      <textarea name="description" placeholder="Event Description" required><?php echo htmlspecialchars($edit_description); ?></textarea>
      <input type="file" name="image" accept="image/*">

      <?php if ($edit_mode && $edit_image) { ?>
          <p>Current Image:</p>
          <img src="public/images/<?php echo $edit_image; ?>" alt="Current Image" style="width:120px; display:block; margin-bottom:10px;">
      <?php } ?>

      <button type="submit" name="<?php echo $edit_mode ? 'update_event' : 'add_event'; ?>">
          <?php echo $edit_mode ? 'Update Event' : 'Add Event'; ?>
      </button>
  </form>
</div>

<!-- EVENTS LIST -->
<main class="announcements-container">
<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
  <div class="announcement-card">
    <div class="thumb">
      <img src="public/images/<?php echo $row['image']; ?>" alt="Event Image">
    </div>
    <div class="content">
      <h2 class="title"><?php echo $row['title']; ?></h2>
      <p class="date"><?php echo $row['event_date']; ?></p>
      <p class="description"><?php echo $row['description']; ?></p>

      <a href="events.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
      <a href="events.php?edit=<?php echo $row['id']; ?>" class="delete-btn" style="background: linear-gradient(135deg, #4361ee, #4cc9f0); margin-left:10px;">Edit</a>
    </div>
  </div>
<?php
    }
} else {
    echo "<p style='text-align:center;'>No events available.</p>";
}
?>
</main>

<footer>
© 2025 BSIT Department - Cebu Technological University Tabuelan Campus
</footer>

<script>
const sideMenu = document.getElementById("sideMenu");
const hamburger = document.querySelector(".hamburger");

hamburger.addEventListener("click", () => sideMenu.classList.toggle("open"));
document.addEventListener("click", (e) => {
    if (!sideMenu.contains(e.target) && !hamburger.contains(e.target)) {
        sideMenu.classList.remove("open");
    }
});
</script>
</body>
</html>