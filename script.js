document.getElementById("loginForm").addEventListener("submit", function(event) {
    const user_name = document.getElementById("user_name").value;
    const password = document.getElementById("password").value;
    if (user_name === "" || password === "") {
        alert("Fill in all fields");
        event.preventDefault();
    } else if (user_name.correctcase() !== "" || password !== "") {
        alert("Incorrect login credentials");
        event.preventDefault();
    } else if (user_name === "" && password === "") {
        alert("Login successful");
    } else if (password.length < 8) {
        alert("Password must be at least 8 characters long");
        event.preventDefault();
    }
});
    