<?php
/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "bsit_portal");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* ===== INITIALIZE VARIABLES ===== */
$edit_mode = false;
$edit_id = 0;
$edit_title = '';
$edit_date = '';
$edit_description = '';
$edit_image = '';

/* ===== CHECK IF EDIT MODE ===== */
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM events WHERE id=$edit_id");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_row = mysqli_fetch_assoc($edit_result);
        $edit_title = $edit_row['title'];
        $edit_date = $edit_row['event_date'];
        $edit_description = $edit_row['description'];
        $edit_image = $edit_row['image'];
        $edit_mode = true;
    }
}

/* ADD EVENT */
if (isset($_POST['add_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    /* IMAGE UPLOAD */
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
            echo "❌ Image upload failed.";
        }
    } else {
        echo "❌ No image selected.";
    }
}

/* UPDATE EVENT */
if (isset($_POST['update_event']) && $edit_mode) {
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
/* ===== ENHANCED ADMIN FORM ===== */
.admin-form {
    background: linear-gradient(135deg, #ffffff, #f3f6ff);
    color: #000;
    padding: 30px;
    max-width: 650px;
    margin: 140px auto 40px;
    border-radius: 14px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
}

/* Decorative top bar */
.admin-form::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, #4cc9f0, #4361ee);
}

.admin-form h2 {
    margin-bottom: 20px;
    font-size: 26px;
    text-align: center;
    color: #0a1a33;
}

.admin-form input,
.admin-form textarea {
    width: 100%;
    padding: 14px;
    margin-bottom: 14px;
    border-radius: 8px;
    border: 1px solid #d0d7ff;
    font-size: 15px;
    transition: 0.3s ease;
}

.admin-form textarea {
    resize: vertical;
    min-height: 120px;
}

.admin-form input:focus,
.admin-form textarea:focus {
    border-color: #4361ee;
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
}

.admin-form button {
    width: 100%;
    background: linear-gradient(135deg, #4361ee, #4cc9f0);
    color: #fff;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
}

.admin-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(67, 97, 238, 0.35);
}

/* Success message */
.success {
    text-align: center;
    background: #e6fff4;
    color: #0f5132;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 500;
}

/* Action buttons */
.delete-btn {
    display: inline-block;
    color: #fff;
    padding: 8px 14px;
    margin-top: 12px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    transition: 0.3s ease;
}

.delete-btn.delete {
    background: linear-gradient(135deg, #e63946, #ff6b6b);
}

.delete-btn.edit {
    background: linear-gradient(135deg, #4361ee, #4cc9f0);
    margin-left: 10px;
}

.delete-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 18px rgba(0,0,0,0.2);
}
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

<!-- ADD/EDIT EVENT FORM -->
<div class="admin-form">
    <h2><?php echo $edit_mode ? 'Edit Event' : 'Add New Event'; ?></h2>

    <?php if (isset($_GET['success'])) { 
        if ($_GET['success']==1) echo '<p class="success">Event added successfully!</p>';
        if ($_GET['success']==2) echo '<p class="success">Event updated successfully!</p>';
    } ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Event Title" required
               value="<?php echo htmlspecialchars($edit_title ?? ''); ?>">
        <input type="text" name="event_date" placeholder="Event Date (e.g. Jan 31, 2025 · 6:00 PM)" required
               value="<?php echo htmlspecialchars($edit_date ?? ''); ?>">
        <textarea name="description" placeholder="Event Description" required><?php echo htmlspecialchars($edit_description ?? ''); ?></textarea>
        <input type="file" name="image" accept="image/*">

        <?php if ($edit_mode && !empty($edit_image)) { ?>
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
if ($result && mysqli_num_rows($result) > 0) {
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

            <a href="events.php?delete=<?php echo $row['id']; ?>" 
               class="delete-btn delete"
               onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>

            <a href="events.php?edit=<?php echo $row['id']; ?>" 
               class="delete-btn edit">Edit</a>
        </div>
    </div>
<?php
    }
} else {
    echo "<p style='text-align:center;'>No events available.</p>";
}
?>
</main>

<footer>© 2025 BSIT Department - Cebu Technological University Tabuelan Campus</footer>

<script>
const sideMenu = document.getElementById("sideMenu");
const hamburger = document.querySelector(".hamburger");

hamburger.addEventListener("click", () => {
    sideMenu.classList.toggle("open");
});

document.addEventListener("click", (e) => {
    if (!sideMenu.contains(e.target) && !hamburger.contains(e.target)) {
        sideMenu.classList.remove("open");
    }
});
</script>

</body>
</html>
