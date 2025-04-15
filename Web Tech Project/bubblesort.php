<?php
// Default array values
$array = [];
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_elements = isset($_POST['num_elements']) ? (int)$_POST['num_elements'] : 10;
    
    // Option 1: Randomize numbers
    if (isset($_POST['option']) && $_POST['option'] == 'randomize') {
        // Generate random numbers between 0-99
        $array = range(0, 99);
        shuffle($array);
        $array = array_slice($array, 0, $num_elements);
    }

    // Option 2: User input with no duplicates
    elseif (isset($_POST['option']) && $_POST['option'] == 'user_input') {
        $user_input = explode(",", $_POST['user_input']);
        $user_input = array_map('trim', $user_input);

        // Check for duplicates and validate input
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
    <title>Bubble Sort Animation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<button id="theme-toggle" class="theme-toggle-button">Switch to Dark Theme</button>
<button id="theme-toggle" class="theme-toggle-button"><a href="index.html" style=" text-decoartion: none;">Back to Home Page</a></button>
<div class="container">
    <h2>Bubble Sort Visualization</h2>

    <!-- Form for user input -->
    <form method="post">
        <label for="num_elements">Number of elements: </label>
        <input type="number" id="num_elements" name="num_elements" value="10" min="2" max="100" required><br><br>

        <label for="randomize">Randomize numbers (between 0-99): </label>
        <input type="radio" id="randomize" name="option" value="randomize" checked><br>

        <br>
        <label for="user_input">Enter your numbers (comma-separated, no duplicates): </label>
        <input type="radio" id="user_input" name="option" value="user_input">

        <textarea id="user_input_field" name="user_input" rows="4" cols="50" placeholder="Enter numbers separated by commas..." style="display: none;"></textarea><br><br>

        <button type="submit">Generate Array</button>
    </form>

    <?php
    // Display any error messages
    if ($error_message) {
        echo "<p style='color: red;'>$error_message</p>";
    }

    // Show the array if it's populated
    if (!empty($array)) {
        // Display the numbers in the array
        echo "<h4>Numbers in the Array:</h4>";
        echo "<p>" . implode(", ", $array) . "</p>";
    }
    ?>

    <div id="array-container" style="position: relative; height: 400px;">
        <?php
        // Find the maximum value in the array
        $max_value = max($array);

        // Display the array as bars with relative heights (inverted)
        if (!empty($array)) {
            $bar_width = 30; // Bar width
            $spacing = 10;  // Spacing between bars
            foreach ($array as $index => $value) {
                // Calculate relative height
                $height = ($value / $max_value) * 400; // Normalized height
                $left = ($bar_width + $spacing) * $index; // Calculate left position based on index
                echo "<div class='bar' style='height: {$height}px; left: {$left}px;' data-value='$value' >$value</div>";
            }
        }
        ?>
    </div>

    <!-- Modal for sorting completion -->
    <div id="overlay"></div>
    <div id="completion-modal">
        <h3>Sorting Completed!</h3>
        <button onclick="closeModal()">Close</button>
    </div>
</div>
<script>
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
    
    // Toggle visibility of the input field based on selected option
    document.getElementById('randomize').addEventListener('change', function() {
        document.getElementById('user_input_field').style.display = 'none';  // Hide the user input field
    });

    document.getElementById('user_input').addEventListener('change', function() {
        document.getElementById('user_input_field').style.display = 'block';  // Show the user input field
    });

    // Disable all buttons during sorting
    function disableButtons() {
        document.querySelectorAll("button").forEach(button => {
            button.disabled = true;
        });
        // Disable the input fields as well
        document.querySelectorAll("input[type='number'], input[type='radio'], textarea").forEach(input => {
            input.disabled = true;
        });
    }

    // Enable all buttons and input fields after sorting is complete
    function enableButtons() {
        document.querySelectorAll("button").forEach(button => {
            button.disabled = false;
        });
        // Enable the input fields
        document.querySelectorAll("input[type='number'], input[type='radio'], textarea").forEach(input => {
            input.disabled = false;
        });
    }

    // Clear the form input fields after sorting
    function clearForm() {
        document.getElementById('num_elements').value = 10;  // Reset to default
        document.getElementById('randomize').checked = true;  // Reset to randomize by default
        document.getElementById('user_input_field').value = '';  // Clear user input field
    }

    // Bubble sort logic with animation
    const bars = document.querySelectorAll('.bar');
    let arr = <?php echo json_encode($array); ?>;
    let comparisons = 0;
    let swaps = 0;
    let i = 0, j = 0;

    function swapElements(index1, index2) {
        // Swap array elements
        let temp = arr[index1];
        arr[index1] = arr[index2];
        arr[index2] = temp;

        // Swap DOM elements (bars)
        const bar1 = bars[index1];
        const bar2 = bars[index2];

        // Create a sliding effect
        bar1.style.transform = `translateX(${bar2.offsetLeft - bar1.offsetLeft}px)`;
        bar2.style.transform = `translateX(${bar1.offsetLeft - bar2.offsetLeft}px)`;

        // Temporarily apply height for smooth transition
        const tempHeight = bar1.style.height;
        bar1.style.height = bar2.style.height;
        bar2.style.height = tempHeight;

        // Swap text
        const tempText = bar1.innerText;
        bar1.innerText = bar2.innerText;
        bar2.innerText = tempText;

        // After the swap, reset the transforms after animation completes
        setTimeout(() => {
            bar1.style.transform = 'translateX(0)';
            bar2.style.transform = 'translateX(0)';
        }, 300);  // Match the duration of the animation
    }

    function highlight(index1, index2) {
        // Highlight the bars being compared
        bars[index1].classList.add('highlight');
        bars[index2].classList.add('highlight');
    }

    function unhighlight(index1, index2) {
        // Remove highlight
        bars[index1].classList.remove('highlight');
        bars[index2].classList.remove('highlight');
    }

    function logSorting(array) {
            const dataset = JSON.stringify(array);
            const arrayState = JSON.stringify(array);
            
            fetch('log_sort.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `dataset=${dataset}&comparisons=${comparisons}&swaps=${swaps}&array_state=${arrayState}`
            })
            .then(response => response.json())
            .then(logs => {
                // Update the log list
                logList.innerHTML = "";
                logs.forEach(log => {
                    const logEntry = document.createElement('li');
                    logEntry.textContent = `Timestamp: ${log.timestamp}, Array: ${log.array_state}, Comparisons: ${log.comparisons}, Swaps: ${log.swaps}`;
                    logList.appendChild(logEntry);
                });
            })
            .catch(error => console.error('Error logging sorting data:', error));
        }

    function bubbleSort() {
        if (isSorting) {
            if (i < arr.length) {
                if (j < arr.length - i - 1) {
                    highlight(j, j + 1);
                    if (arr[j] > arr[j + 1]) {
                        const timeoutId = setTimeout(() => {
                            swapElements(j, j + 1);
                            unhighlight(j, j + 1);
                            comparisons++;
                            j++;
                            bubbleSort();
                        }, 300); // Slowed down the swap time
                        sortingTimeouts.push(timeoutId); // Save the timeout ID
                    } else {
                        const timeoutId = setTimeout(() => {
                            unhighlight(j, j + 1);
                            j++;
                            bubbleSort();
                        }, 300); // Slowed down the swap time
                        sortingTimeouts.push(timeoutId); // Save the timeout ID
                    }
                } else {
                    i++;
                    j = 0;
                    bubbleSort();
                }
            } else {
                // Sorting completed, show the modal, enable buttons and clear the form
                enableButtons();
                clearForm();  // Reset the form fields
                document.getElementById('overlay').style.display = 'block';
                document.getElementById('completion-modal').style.display = 'block';
            }
        }
    }

    function closeModal() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('completion-modal').style.display = 'none';
    }

    // Start sorting when page loads
    isSorting = true;
    disableButtons();  // Disable buttons before starting the sorting
    bubbleSort();
</script>
</body>
</html>
