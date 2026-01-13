<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food Review</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6">
        <h2 class="text-2xl font-bold mb-4 text-center text-indigo-600">Post a Food Review</h2>
        
        <div id="food-quote" class="mb-6 p-4 bg-yellow-100 rounded text-sm italic text-gray-700">
            Loading food inspiration...
        </div>

        <form id="reviewForm" enctype="multipart/form-data">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Food Name</label>
                <input type="text" name="food_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g. Nasi Lemak" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Review</label>
                <textarea name="review_text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="How did it taste?"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Take a Photo</label>
                <input type="file" name="food_image" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-full file:border-0
                  file:text-sm file:font-semibold
                  file:bg-indigo-50 file:text-indigo-700
                  hover:file:bg-indigo-100" required>
                <p class="text-xs text-gray-500 mt-1">Tap to open camera on mobile.</p>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit Review
            </button>
        </form>
    </div>

    <script>
        // REQUIREMENT: Web API Integration (TheMealDB)
        // We fetch a random meal to show as "Inspiration"
        $(document).ready(function() {
            $.ajax({
                url: 'https://www.themealdb.com/api/json/v1/1/random.php',
                method: 'GET',
                success: function(data) {
                    const meal = data.meals[0];
                    $('#food-quote').html(`<strong>Today's Pick:</strong> Have you tried making <em>${meal.strMeal}</em>? It's a ${meal.strArea} dish!`);
                },
                error: function() {
                    $('#food-quote').text('Share your delicious food with us!');
                }
            });
        });
    </script>
</body>
</html>