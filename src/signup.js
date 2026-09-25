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
                buttonText.classList.toggle('hidden')
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

    // Mark the field as invalid so screen readers report it
    if (errorField) {
        formElement.querySelector(`input[name=${errorField}]`)?.setAttribute('aria-invalid', 'true')
    }
}

const clearErrors = (formElement) => {
    const errors = formElement.querySelectorAll("output.active")
    errors.forEach(error => error.classList.remove("active"))
    formElement.querySelectorAll('[aria-invalid]').forEach(field => field.removeAttribute('aria-invalid'))
}

const processSignup = async (formElement) => {
    clearErrors(formElement)

    const formData = new FormData(formElement)
    const userData = {
        email: formData.get('email') ?? '',
        password: formData.get('password') ?? '',
        password_confirm: formData.get('password_confirm') ?? ''
    }

    try {
        toggleLoadingState(formElement)

        const response = await fetch('/includes/endpoints/handle-user-signup.php', {
            method: 'POST',
            body: JSON.stringify(userData),
            headers: {
                'Content-Type': 'application/json'
            }
        })

        const result = await response.json()

        if (result?.status !== 200) {
            const errorField = result?.data?.error_field ?? null
            const errorMessage = result?.data?.error_message

            if (errorMessage) {
                showErrorMessage(formElement, errorField, errorMessage)
            }
        } else {
            const signupSuccessMessage = result.message

            if (signupSuccessMessage) {
                // Show success toast notification
                window.dispatchEvent(
                    new CustomEvent('showtoast', {
                        detail: {
                            toastId: 'account-created-toast-notification',
                            message: signupSuccessMessage
                        }
                    })
                )

                formElement.reset()
            }
        }
    } catch (error) {
        console.error(error)
    } finally {
        toggleLoadingState(formElement)
    }
}

const signupForm = document.getElementById('signup-form')

if (signupForm) {
    signupForm.addEventListener('submit', (event) => {
        event.preventDefault()

        const formElement = event.currentTarget
        if (!formElement) return

        processSignup(formElement)
    })
}