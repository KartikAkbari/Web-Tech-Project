<?php
// Default array values
$array = range(0, 99);
shuffle($array);
$array = array_slice($array, 0, 10); // Default to 10 elements
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_elements = isset($_POST['num_elements']) ? (int)$_POST['num_elements'] : 10;

    if (isset($_POST['option']) && $_POST['option'] == 'randomize') {
        $array = range(0, 99);
        shuffle($array);
        $array = array_slice($array, 0, $num_elements);
    }

    if (isset($_POST['option']) && $_POST['option'] == 'user_input') {
        $user_input = explode(",", $_POST['user_input']);
        $user_input = array_map('trim', $user_input);

        if (count($user_input) !== count(array_unique($user_input))) {
            $error_message = "Please enter unique numbers.";
        } else {
            foreach ($user_input as $value) {
                if (!is_numeric($value) || $value < 0 || $value > 999) {
                    $error_message = "All numbers must be between 0 and 999.";
                    break;
                }
            }
            if (!$error_message) {
                $array = $user_input;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merge Sort Visualization</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 20px;
}

.container {
    width: 80%;
    max-width: 900px;
    margin: 0 auto;
    text-align: center;
    background-color: white;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 20px;
}

.bar {
    display: inline-block;
    border-radius: 10px;
    width: 30px;
    margin: 2px;
    background-color: lightblue;
    text-align: center;
    color: white;
    font-weight: bold;
    line-height: 25px;
    transition: transform 0.3s ease, height 0.3s ease;
    transform-origin: bottom center;
    min-height: 30px;
}

.highlight {
    background-color: red;
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.8);
}

#completion-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    text-align: center;
    z-index: 1000;
}

#completion-modal h3 {
    margin: 0;
    font-size: 1.5em;
    color: green;
}

#completion-modal button {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: green;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#completion-modal button:hover {
    background-color: darkgreen;
}

#overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

form {
    margin-bottom: 20px;
}

textarea {
    width: 100%;
    margin-top: 10px;
}

#array-container {
    position: relative;
    height: 400px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
body.light-theme {
    background-color: #ffffff;
    color: #000000;
}

body.dark-theme {
    background-color: #121212;
    color: #ffffff;
}

.container {
    background-color: inherit;
    color: inherit;
}
.bar.dark-theme {
    background-color: #4caf50;
}
body.dark-theme .bar {
    background-color: #bb86fc;
}
.theme-toggle-button {
    background: linear-gradient(135deg, #4caf50, #81c784);
    border: none;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 30px;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
}

.theme-toggle-button:hover {
    background: linear-gradient(135deg, #388e3c, #66bb6a);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.theme-toggle-button:active {
    transform: scale(0.95);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

body.dark-theme .theme-toggle-button {
    background: linear-gradient(135deg, #bb86fc, #9575cd);
}

body.dark-theme .theme-toggle-button:hover {
    background: linear-gradient(135deg, #7b1fa2, #9c27b0);
}
    </style>
</head>
<body>
<button id="theme-toggle" class="theme-toggle-button">Switch to Dark Theme</button>
<button id="theme-toggle" class="theme-toggle-button"><a href="index.html" style=" text-decoartion: none;">Back to Home Page</a></button>
<div class="container">
    <h2>Merge Sort Visualization</h2>

    <form method="post">
        <label for="num_elements">Number of elements: </label>
        <input type="number" id="num_elements" name="num_elements" min="2" max="100" required><br><br>

        <label for="randomize">Randomize numbers (between 0-99): </label>
        <input type="radio" id="randomize" name="option" value="randomize" checked><br>

        <label for="user_input">Enter your numbers (comma-separated, no duplicates): </label>
        <input type="radio" id="user_input" name="option" value="user_input">
        <textarea id="user_input_field" name="user_input" rows="4" cols="50" placeholder="Enter numbers separated by commas..." style="display: none;"></textarea><br><br>

        <button type="submit">Generate Array</button>
    </form>

    <?php
    if ($error_message) {
        echo "<p style='color: red;'>$error_message</p>";
    }

    if (!empty($array)) {
        echo "<h4>Numbers in the Array:</h4>";
        echo "<p>" . implode(", ", $array) . "</p>";
    }
    ?>

    <div id="array-container">
        <?php
        $max_value = max($array);
        foreach ($array as $value) {
            $height = ($value / $max_value) * 300;
            echo "<div class='bar' style='height: {$height}px;' data-value='$value'>$value</div>";
        }
        ?>
    </div>

    <div id="overlay"></div>
    <div id="completion-modal">
        <h3>Sorting Completed!</h3>
        <button onclick="closeModal()">Close</button>
    </div>
</div>

<script>
    document.getElementById('randomize').addEventListener('change', function () {
        document.getElementById('user_input_field').style.display = 'none';
    });

    document.getElementById('user_input').addEventListener('change', function () {
        document.getElementById('user_input_field').style.display = 'block';
    });

    const bars = document.querySelectorAll('.bar');
    let arr = <?php echo json_encode($array); ?>;
    let steps = [];

    function mergeSort(arr, l, r) {
        if (l >= r) return;
        const mid = Math.floor((l + r) / 2);
        mergeSort(arr, l, mid);
        mergeSort(arr, mid + 1, r);
        merge(arr, l, mid, r);
    }

    function merge(arr, l, mid, r) {
        const left = arr.slice(l, mid + 1);
        const right = arr.slice(mid + 1, r + 1);
        let i = 0, j = 0, k = l;

        while (i < left.length && j < right.length) {
            if (left[i] <= right[j]) {
                steps.push([k, left[i]]);
                arr[k++] = left[i++];
            } else {
                steps.push([k, right[j]]);
                arr[k++] = right[j++];
            }
        }

        while (i < left.length) {
            steps.push([k, left[i]]);
            arr[k++] = left[i++];
        }

        while (j < right.length) {
            steps.push([k, right[j]]);
            arr[k++] = right[j++];
        }
    }

    function animateMergeSort() {
        if (steps.length === 0) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('completion-modal').style.display = 'block';
            enableInputs();
            return;
        }
        const [index, value] = steps.shift();
        const bar = bars[index];
        bar.style.height = `${(value / Math.max(...arr)) * 300}px`;
        bar.innerText = value;
        setTimeout(animateMergeSort, 100);
    }

    function disableInputs() {
        document.querySelectorAll("button, input, textarea").forEach(el => el.disabled = true);
    }

    function enableInputs() {
        document.querySelectorAll("button, input, textarea").forEach(el => el.disabled = false);
    }

    function closeModal() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('completion-modal').style.display = 'none';
    }

    disableInputs();
    mergeSort(arr, 0, arr.length - 1);
    animateMergeSort();

    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const currentTheme = localStorage.getItem('theme') || 'light-theme';
    body.classList.add(currentTheme);
    themeToggle.textContent = currentTheme === 'light-theme' ? 'Switch to Dark Theme' : 'Switch to Light Theme';

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        body.classList.toggle('light-theme');
        const newTheme = body.classList.contains('dark-theme') ? 'dark-theme' : 'light-theme';
        themeToggle.textContent = newTheme === 'light-theme' ? 'Switch to Dark Theme' : 'Switch to Light Theme';
        localStorage.setItem('theme', newTheme);
    });
</script>
</body>
</html>
