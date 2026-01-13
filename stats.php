<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Food Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6">
        
        <a href="add_review.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold mb-4 inline-block">&larr; Back to Add Review</a>
        
        <h2 class="text-2xl font-bold mb-6 text-center text-indigo-600">Campus Food Trends</h2>

        <div class="relative h-64 w-full">
            <canvas id="ratingsChart"></canvas>
        </div>

        <div class="mt-6 text-center">
            <p class="text-gray-600 text-sm">See how students are rating the food!</p>
        </div>
    </div>

    <script>
        // Setup Chart.js
        const ctx = document.getElementById('ratingsChart').getContext('2d');
        
        // This chart shows how many 5-star vs 1-star reviews exist.
        // (Currently using dummy data. Member 1 will connect this to the database later)
        const myChart = new Chart(ctx, {
            type: 'doughnut', // You can change this to 'bar', 'line', or 'pie'
            data: {
                labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
                datasets: [{
                    label: '# of Reviews',
                    data: [12, 19, 3, 5, 2], 
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Green
                        'rgba(54, 162, 235, 0.6)', // Blue
                        'rgba(255, 206, 86, 0.6)', // Yellow
                        'rgba(255, 159, 64, 0.6)', // Orange
                        'rgba(255, 99, 132, 0.6)'  // Red
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>