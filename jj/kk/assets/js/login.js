document.addEventListener("DOMContentLoaded", () => {
  // Add animation delay to form elements
  const formElements = document.querySelectorAll(".form-control, .btn, .alert, .mt-3")
  formElements.forEach((element, index) => {
    element.style.animationDelay = `${index * 0.1}s`
  })

  // Password visibility toggle
  const passwordField = document.getElementById("password")
  const togglePassword = document.getElementById("togglePassword")

  if (togglePassword) {
    togglePassword.addEventListener("click", function () {
      const type = passwordField.getAttribute("type") === "password" ? "text" : "password"
      passwordField.setAttribute("type", type)

      // Toggle icon
      this.classList.toggle("fa-eye")
      this.classList.toggle("fa-eye-slash")
    })
  }

  // Form validation
  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    loginForm.addEventListener("submit", (event) => {
      let isValid = true
      const email = document.getElementById("email")
      const password = document.getElementById("password")

      // Simple email validation
      if (!email.value.includes("@") || !email.value.includes(".")) {
        showError(email, "Please enter a valid email address")
        isValid = false
      } else {
        removeError(email)
      }

      // Password validation
      if (password.value.length < 6) {
        showError(password, "Password must be at least 6 characters")
        isValid = false
      } else {
        removeError(password)
      }

      if (!isValid) {
        event.preventDefault()
      }
    })
  }

  // Helper functions for validation
  function showError(input, message) {
    const formGroup = input.parentElement
    let errorElement = formGroup.querySelector(".error-message")

    if (!errorElement) {
      errorElement = document.createElement("div")
      errorElement.className = "error-message"
      errorElement.style.color = "#d32f2f"
      errorElement.style.fontSize = "12px"
      errorElement.style.marginTop = "5px"
      formGroup.appendChild(errorElement)
    }

    errorElement.textContent = message
    input.style.borderColor = "#d32f2f"
  }

  function removeError(input) {
    const formGroup = input.parentElement
    const errorElement = formGroup.querySelector(".error-message")

    if (errorElement) {
      formGroup.removeChild(errorElement)
    }

    input.style.borderColor = ""
  }
})
