<?php
/**
 * This is the maon page used to display the diary entries
 * still in prototype
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */

require_once "../../src/config/config.php";
require_once "../../src/database/database-diary.php";
require_once "../../src/session/session.php";

// start session and ignore warning
session_start();
error_reporting(E_ERROR | E_PARSE);

if (!checkSession()) {
    destroySession();
    header("Location: ../../public/authentication/auth.php");
    exit();
}

// get diary and query status
$diaries = getDiaries();
$status = $diaries[0];
unset($diaries[0]);

// get selected diary, id of the diary entry
$selectedDiary = -1;
if (isset($_POST["select"])) {
    $selectedDiary = $_POST["select"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main - Prototype</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="shortcut icon" href="../../assets/favlogo.svg" type="image/svg+xml">
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center">Diary Management</h1>

            <!-- Diary list -->
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Last Modified</th>
                    <th>Content</th>
                    <th>Mood</th>
                    <th>Tags</th>
                    <th>Select</th>
                    <th>Delete</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($status == 1): ?>
                    <tr>
                        <td colspan="6">No diary entries</td>
                    </tr>
                <?php elseif ($status == -1): ?>
                    <tr>
                        <td colspan="6">Database isn't connected</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($diaries as $diary): ?>
                        <tr>
                            <td><?php echo $diary["id"]; ?></td>
                            <td><?php echo $diary["date"]; ?></td>
                            <td><?php echo $diary["date_modified"]; ?></td>
                            <td><?php echo nl2br($diary["content"]); ?></td>
                            <td><?php echo $diary["mood"]; ?></td>
                            <td><?php echo implode(", ", explode(",", $diary["tags"])); ?></td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                    <input type="hidden" name="select" value="<?php echo $diary["id"]; ?>">
                                    <button type="submit" class="btn btn-primary">Select</button>
                                </form>
                            </td>
                            <td>
                                <form action="../index/handler.php" method="post">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $diary["id"]; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- ############################################################# -->

            <?php if ($selectedDiary == -1): ?>
                <h1 class="text-center">Add diary</h1>

                <form action="../index/handler.php" method="post">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group mb-3">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" class="form-control"
                                  style="resize: none" rows="1" oninput="adjustTextareaSize()" required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="tags">Tags:</label>
                        <input type="text" id="tags" name="tags" class="form-control"
                               placeholder="Enter tags separated by commas">
                    </div>

                    <div class="form-group mb-3">
                        <label for="mood">Mood:</label>
                        <select id="mood" name="mood" class="form-control" required>
                            <option value="happy">Happy</option>
                            <option value="sad">Sad</option>
                            <option value="angry">Angry</option>
                            <option value="neutral">Neutral</option>
                            <option value="excited">Excited</option>
                            <option value="tired">Tired</option>
                            <option value="sick">Sick</option>
                            <option value="scared">Scared</option>
                            <option value="surprised">Surprised</option>
                            <option value="bored">Bored</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            <?php else: ?>
                <h1 class="text-center">Edit diary</h1>

                <?php
                // search the id and get the content
                $selectedDiaryContent = [];
                foreach ($diaries as $diary) {
                    if ($diary["id"] == $selectedDiary) {
                        $selectedDiaryContent = $diary;
                        break;
                    }
                }
                ?>

                <form action="../index/handler.php" method="post">
                    <input type="hidden" name="action" value="modify">
                    <input type="hidden" name="id" value="<?php echo $selectedDiaryContent["id"]; ?>">

                    <div class="form-group mb-3">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" class="form-control"
                                  style="resize: none" rows="1" oninput="adjustTextareaSize()"
                                  required><?php echo $selectedDiaryContent["content"]; ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="tags">Tags:</label>
                        <input type="text" id="tags" name="tags" class="form-control"
                               placeholder="Enter tags separated by commas"
                               value="<?php echo implode(", ", explode(",", $selectedDiaryContent["tags"])); ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="mood">Mood:</label>
                        <select id="mood" name="mood" class="form-control" required>
                            <option value="happy" <?php if ($selectedDiaryContent["mood"] == "happy") echo "selected"; ?>>
                                Happy
                            </option>
                            <option value="sad" <?php if ($selectedDiaryContent["mood"] == "sad") echo "selected"; ?>>
                                Sad
                            </option>
                            <option value="angry" <?php if ($selectedDiaryContent["mood"] == "angry") echo "selected"; ?>>
                                Angry
                            </option>
                            <option value="neutral" <?php if ($selectedDiaryContent["mood"] == "neutral") echo "selected"; ?>>
                                Neutral
                            </option>
                            <option value="excited" <?php if ($selectedDiaryContent["mood"] == "excited") echo "selected"; ?>>
                                Excited
                            </option>
                            <option value="tired" <?php if ($selectedDiaryContent["mood"] == "tired") echo "selected"; ?>>
                                Tired
                            </option>
                            <option value="sick" <?php if ($selectedDiaryContent["mood"] == "sick") echo "selected"; ?>>
                                Sick
                            </option>
                            <option value="scared" <?php if ($selectedDiaryContent["mood"] == "scared") echo "selected"; ?>>
                                Scared
                            </option>
                            <option value="surprised" <?php if ($selectedDiaryContent["mood"] == "surprised") echo "selected"; ?>>
                                Surprised
                            </option>
                            <option value="bored" <?php if ($selectedDiaryContent["mood"] == "bored") echo "selected"; ?>>
                                Bored
                            </option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
<script>
    if (typeof window.history.pushState == 'function') {
        window.history.pushState({}, "Hide", '<?php echo $_SERVER['PHP_SELF'];?>');
    }

    function adjustTextareaSize() {
        // Get the textarea element
        var textarea = document.getElementById("content");

        // Calculate the number of rows needed based on the content
        // Set the rows attribute to the calculated value
        textarea.rows = textarea.value.split("\n").length;
    }

    window.onload = function () {
        adjustTextareaSize();
    };
</script>
</body>
</html>