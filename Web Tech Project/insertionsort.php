<?php
// Default array values
$array = range(0, 99);
shuffle($array);
$array = array_slice($array, 0, 10); // Default to 10 elements
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_elements = isset($_POST['num_elements']) ? (int)$_POST['num_elements']:10;

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
    <title>Insertion Sort Visualization</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<button id="theme-toggle" class="theme-toggle-button">Switch to Dark Theme</button>
<button id="theme-toggle" class="theme-toggle-button"><a href="index.html" style=" text-decoartion: none;">Back to Home Page</a></button>
<div class="container">
    <h2>Insertion Sort Visualization</h2>

    <!-- Form for user input -->
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
           const bars = document.querySelectorAll('.bar');
let arr = <?php echo json_encode($array); ?>;
let originalArray = [...arr]; 
let i = 1, j = 0;
let comparisons = 0;
let swaps = 0;
let startTime, endTime; // Variables for measuring execution time

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

// Start measuring time before the sorting starts
startTime = performance.now();

function insertionSortStep() {
    disableInputs();
    if (i < arr.length) {
        let key = arr[i];
        j = i - 1;
        bars[i].classList.add('highlight');

        function shiftBars() {
            comparisons++; // Count comparison
            if (j >= 0 && arr[j] > key) {
                arr[j + 1] = arr[j];
                bars[j + 1].style.height = bars[j].style.height;
                bars[j + 1].innerText = bars[j].innerText;
                j--;
                setTimeout(shiftBars, 50);
            } else {
                arr[j + 1] = key;
                bars[j + 1].style.height = `${(key / Math.max(...arr)) * 300}px`;
                bars[j + 1].innerText = key;
                bars[i].classList.remove('highlight');
                swaps++; // Count swap
                i++;
                setTimeout(insertionSortStep, 500);
            }
        }

        shiftBars();
    } else {
        // Stop measuring time after sorting is complete
        endTime = performance.now();
        const executionTime = ((endTime - startTime) / 1000).toFixed(4); // Convert milliseconds to seconds

        enableInputs();
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('completion-modal').style.display = 'block';

        // Log performance data including execution time
        logPerformance(executionTime);
    }
}

// Log sorting performance to server
function logPerformance(executionTime) {
    const logData = {
        algorithmName: 'Insertion Sort',
        dataset: originalArray,
        sortedData: arr,  // In real-case, sorted array would be the result after the sort.
        executionTime: executionTime,  // Now includes the correct execution time
        comparisons: comparisons, // Actual number of comparisons
        swaps: swaps // Actual number of swaps
    };

    console.log('Log Data:', logData);

    fetch('log_sort.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(logData)
    })
    .then(response => response.text())
    .then(data => console.log('Log saved:', data))
    .catch(error => console.error('Error:', error));
}

// Start sorting immediately if array is populated
if (arr.length > 0) {
    insertionSortStep();
}

    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Set default theme based on user preference or default to light
    const currentTheme = localStorage.getItem('theme') || 'light-theme';
    body.classList.add(currentTheme);
    themeToggle.textContent = currentTheme === 'light-theme' ? 'Switch to Dark Theme' : 'Switch to Light Theme';

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        body.classList.toggle('light-theme');
        const newTheme = body.classList.contains('dark-theme') ? 'dark-theme' : 'light-theme';
        themeToggle.textContent = newTheme === 'light-theme' ? 'Switch to Dark Theme' : 'Switch to Light Theme';
        localStorage.setItem('theme', newTheme); // Save preference
    });
</script>


</body>
</html>
