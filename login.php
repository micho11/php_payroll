
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAYROLL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Import a Google Font (Optional, but nice) */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

/* Basic Reset and Box Sizing */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    /* Slightly adjusted gradient for a softer feel */
    background: linear-gradient(to bottom, #a1dcf7, #c4eaf7);
    /* Using Poppins font if loaded, otherwise fallback */
    font-family: 'Poppins', 'Verdana', sans-serif;
    color: #444; /* Slightly softer default text color */
    line-height: 1.6; /* Better readability */
}

.cloud-form-container {
    position: relative;
    background-color: #ffffff;
    /* Increased padding for more whitespace */
    padding: 50px 55px;
    /* Slightly less pronounced radius on main body */
    border-radius: 35px;
    /* Softer, more diffused shadow */
    box-shadow: 0 18px 40px rgba(70, 130, 180, 0.15),
                0 10px 20px rgba(70, 130, 180, 0.1);
    max-width: 420px; /* Slightly wider */
    width: 90%;
    text-align: center;
    z-index: 1;
    /* Add a subtle transition for any potential future animations */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Cloud Puffs Styling */
.cloud-form-container::before,
.cloud-form-container::after {
    content: '';
    position: absolute;
    background-color: #ffffff; /* Match container background */
    border-radius: 50%; /* Perfectly circular */
    /* Match the softer shadow style */
    box-shadow: 0 12px 25px rgba(70, 130, 180, 0.12);
    z-index: -1;
    /* Add transitions for smoother effects if needed later */
    transition: transform 0.4s ease;
}

/* Positioning and Sizing of Puffs */
.cloud-form-container::before {
    width: 110px; /* Adjusted size */
    height: 110px;
    top: -60px;   /* Positioned higher */
    left: 15px;   /* Adjusted horizontal position */
}

.cloud-form-container::after {
    width: 145px; /* Larger puff */
    height: 145px;
    top: -55px;   /* Adjusted vertical position */
    right: -20px; /* Slightly overlap edge for organic feel */
}

/* Optional: Subtle hover effect on the cloud container */
.cloud-form-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(70, 130, 180, 0.2),
                0 15px 30px rgba(70, 130, 180, 0.15);
}
.cloud-form-container:hover::before { transform: scale(1.05) translateX(-5px); }
.cloud-form-container:hover::after  { transform: scale(1.08) translateX(5px); } 


.cloud-form-container h2 {
     margin-bottom: 35px; /* More space below heading */
     /* A slightly richer blue */
     color: #3a7bd5; /* Example: #3a7bd5 or keep #4682B4 */
     font-weight: 600; /* Slightly bolder */
     font-size: 1.9em; /* Slightly larger */
     letter-spacing: 0.5px; /* Subtle letter spacing */
}

form label {
    display: block;
    margin-bottom: 10px; /* Increased space */
    color: #555;
    font-weight: 600; /* Bolder labels */
    text-align: left;
    font-size: 0.9em; /* Slightly smaller label text */
    padding-left: 5px; /* Indent label slightly */
}

form input[type="text"] {
    width: 100%; /* Full width, relies on box-sizing */
    padding: 14px 20px; /* Increased padding */
    margin-bottom: 25px; /* More space between fields */
    border: 1px solid #cfeafc; /* Lighter, softer blue border */
    /* Increased border-radius for a softer pill shape */
    border-radius: 25px;
    background-color: #f8fcff; /* Very light blueish background */
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05); /* Softer inset shadow */
    font-size: 1em;
    font-family: inherit; /* Inherit Poppins/Verdana */
    color: #333;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

form input[type="text"]:focus {
    outline: none;
    border-color: #87CEEB; /* Clearer focus border color */
    /* Subtle outer glow on focus */
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.05), 0 0 0 3px rgba(135, 206, 235, 0.3);
}

form input[type="password"] {
    width: 100%; /* Full width, relies on box-sizing */
    padding: 14px 20px; /* Increased padding */
    margin-bottom: 25px; /* More space between fields */
    border: 1px solid #cfeafc; /* Lighter, softer blue border */
    /* Increased border-radius for a softer pill shape */
    border-radius: 25px;
    background-color: #f8fcff; /* Very light blueish background */
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05); /* Softer inset shadow */
    font-size: 1em;
    font-family: inherit; /* Inherit Poppins/Verdana */
    color: #333;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

form input[type="password"]:focus {
    outline: none;
    border-color: #87CEEB; /* Clearer focus border color */
    /* Subtle outer glow on focus */
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.05), 0 0 0 3px rgba(135, 206, 235, 0.3);
}

/* Submit Button Styling */
form input[type="submit"] {
    /* Using a gradient for the button */
    background: linear-gradient(135deg, #87CEEB 0%, #a0d8f8 100%);
    color: #ffffff; /* White text for better contrast */
    border: none;
    padding: 15px 40px; /* Generous padding */
    border-radius: 25px; /* Match input field radius */
    cursor: pointer;
    font-size: 1.15em; /* Slightly larger */
    font-weight: 600; /* Bolder */
    letter-spacing: 0.5px;
    transition: all 0.3s ease; /* Smooth transition for all properties */
    box-shadow: 0 6px 15px rgba(70, 130, 180, 0.25); /* Button shadow */
    margin-top: 15px;
    display: inline-block; /* Ensure transformations apply correctly */
    width: auto; /* Let button size naturally */
    text-transform: uppercase; /* Uppercase text for emphasis */
}

form input[type="submit"]:hover {
    /* Slightly darker/richer gradient on hover */
    background: linear-gradient(135deg, #76c1e3 0%, #90cde8 100%);
    transform: translateY(-3px); /* Lift effect */
    box-shadow: 0 10px 20px rgba(70, 130, 180, 0.35); /* Enhanced shadow on hover */
}

 form input[type="submit"]:active {
     transform: translateY(0); /* Press down effect */
     box-shadow: 0 5px 12px rgba(70, 130, 180, 0.2); /* Reduced shadow when pressed */
     background: linear-gradient(135deg, #6fb8d9 0%, #82c3e0 100%); /* Slightly darker active state */
}

/* Optional: Add styles for Font Awesome icons if you plan to use them */
/* Example: Add an icon inside the button */
form input[type="submit"] { position: relative; padding-right: 50px; } 
form input[type="submit"]::after {
    content: '\f0c2';
    font-family: 'Font Awesome 6 Free';
    font-weight: 800;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1em;
}
    </style>
</head>

<body>
    <div class="cloud-form-container">
        <h2>PAYROLL</h2>
        <form action="payroll.php" method="POST">
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required><br>

            <label for="Lname">Password:</label>
            <input type="password" id="Lname" name="Lname" required> <br>
        
            <input type="submit" value="Sign In" name="btnsub"><br>
        </form>
    </div>
</body>

</html>

