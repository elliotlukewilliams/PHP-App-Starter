const toggleLoadingState = (parentElement) => {
    const loader = parentElement.querySelector('.loader')
    if (loader) {
        loader.classList.toggle('hidden')
        if (loader.classList.contains('hidden')) {
            loader.ariaHidden = 'true'
        } else {
            loader.ariaHidden = 'false'
        }
        const button = loader.closest('button')
        if (button) {
            button.disabled = !button.disabled
            const buttonText = button.querySelector('.btn-text')
            if (buttonText) {
                buttonText.classList.toggle('opacity-0')
            }
        }
    }
}

const showErrorMessage = (formElement, errorField, errorMessage) => {
    const errorInputValidationMessage =
        errorField
            ? formElement
            .querySelector(`input[name=${errorField}]`)
            ?.parentElement
            ?.querySelector('output')
            : formElement.querySelector('output.any-error')

    if (errorInputValidationMessage) {
        errorInputValidationMessage.classList.add('active')
        errorInputValidationMessage.innerHTML = errorMessage
    }
}

const clearErrors = (formElement) => {
    const errors = formElement.querySelectorAll("output.active")
    errors.forEach(error => error.classList.remove("active"))
}

const processLogin = async (formElement) => {
    clearErrors(formElement)

    const formData = new FormData(formElement)
    const userData = {
        email: formData.get('login_email') ?? '',
        password: formData.get('login_password') ?? ''
    }

    try {
        toggleLoadingState(formElement)

        const response = await fetch('/includes/endpoints/handle-user-login.php', {
            method: 'POST',
            body: JSON.stringify(userData)
        })

        const result = await response.json()

        if (result?.status !== 200) {
            const errorField = result?.data?.error_field ?? null
            const errorMessage = result?.data?.error_message

            if (errorMessage) {
                showErrorMessage(formElement, errorField, errorMessage)
            }
        } else {
            window.location.href = '/account'
        }
    } catch (error) {
        console.error(error)
    } finally {
        toggleLoadingState(formElement)
    }
}

const processLogout = async (formElement) => {
    try {
        toggleLoadingState(formElement)

        const response = await fetch('/includes/endpoints/handle-user-logout.php', {
            method: 'POST'
        })

        const result = await response.json()

        if (result?.status !== 200) {
            const errorMessage = result?.data?.error_message
            if (errorMessage) {
                const errorOutput = formElement.querySelector('output.logout-error')
                if (errorOutput) {
                    errorOutput.classList.add('active')
                    errorOutput.innerHTML = errorMessage
                }
            }
        } else {
            window.location.href = '/login'
        }
    } catch (error) {
        console.error(error)
    } finally {
        toggleLoadingState(formElement)
    }
}

// Handle login event listener
const loginForm = document.getElementById('login-form')
if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault()
        const formElement = event.currentTarget
        if (!formElement) return
        processLogin(formElement)
    })
}

// Handle logout event listener
const logoutForm = document.getElementById('logout-form')
if (logoutForm) {
    logoutForm.addEventListener('submit', (event) => {
        event.preventDefault()
        const formElement = event.currentTarget
        if (!formElement) return
        processLogout(formElement)
    })
}