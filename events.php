<?php
/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "bsit_portal");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

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
            if (file_exists("public/images/$edit_image")) unlink("public/images/$edit_image");
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
/* ===== ADMIN FORM ===== */
.admin-form {
    background: linear-gradient(135deg, #ffffff, #f3f6ff);
    color: #000;
    padding: 30px;
    max-width: 650px;
    margin: 140px auto 40px;
    border-radius: 14px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    position: relative;
}
.admin-form h2 { text-align:center; margin-bottom:20px; }
.admin-form input, .admin-form textarea {
    width:100%; padding:14px; margin-bottom:14px; border-radius:8px; border:1px solid #d0d7ff;
}
.admin-form textarea { resize:vertical; min-height:120px; }
.admin-form button {
    width:100%; padding:14px; border:none; border-radius:10px; cursor:pointer;
    background: linear-gradient(135deg, #4361ee, #4cc9f0); color:#fff;
}
.success { text-align:center; background:#e6fff4; color:#0f5132; padding:12px; border-radius:8px; margin-bottom:15px; font-weight:500; }
.delete-btn {
    display:inline-block; padding:8px 14px; border-radius:6px; font-size:14px; text-decoration:none; color:#fff;
}
.delete-btn.delete { background: linear-gradient(135deg, #e63946, #ff6b6b); }
.delete-btn.edit { background: linear-gradient(135deg, #4361ee, #4cc9f0); margin-left:10px; }
</style>
</head>
<body>

<div class="admin-form">
  <h2><?php echo $edit_mode ? 'Edit Event' : 'Add New Event'; ?></h2>

  <?php
  if (!empty($error)) echo "<p class='success'>$error</p>";
  if (isset($_GET['success'])) {
      $msg = $_GET['success']==1 ? "Event added successfully!" : "Event updated successfully!";
      echo "<p class='success'>$msg</p>";
  }
  ?>

  <form method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Event Title" required value="<?php echo htmlspecialchars($edit_title); ?>">
      <input type="text" name="event_date" placeholder="Event Date (e.g. Jan 31, 2025 · 6:00 PM)" required value="<?php echo htmlspecialchars($edit_date); ?>">
      <textarea name="description" placeholder="Event Description" required><?php echo htmlspecialchars($edit_description); ?></textarea>
      <input type="file" name="image" accept="image/*">

      <?php if ($edit_mode && $edit_image) { ?>
          <p>Current Image:</p>
          <img src="public/images/<?php echo $edit_image; ?>" style="width:120px; margin-bottom:10px;">
      <?php } ?>

      <button type="submit" name="<?php echo $edit_mode ? 'update_event' : 'add_event'; ?>">
          <?php echo $edit_mode ? 'Update Event' : 'Add Event'; ?>
      </button>
  </form>
</div>

<main class="announcements-container">
<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
  <div class="announcement-card">
    <div class="thumb">
      <img src="public/images/<?php echo $row['image']; ?>" style="width:120px;">
    </div>
    <div class="content">
      <h2><?php echo $row['title']; ?></h2>
      <p><?php echo $row['event_date']; ?></p>
      <p><?php echo $row['description']; ?></p>
      <a href="events.php?delete=<?php echo $row['id']; ?>" class="delete-btn delete" onclick="return confirm('Are you sure?');">Delete</a>
      <a href="events.php?edit=<?php echo $row['id']; ?>" class="delete-btn edit">Edit</a>
    </div>
  </div>
<?php
    }
} else {
    echo "<p style='text-align:center;'>No events available.</p>";
}
?>
</main>
</body>
</html>
