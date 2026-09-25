const cancelPasswordReset = (event) => {
    // Reset form field before closing
    const passwordResetRequestForm = document.getElementById('password-reset-request-form')
    if (passwordResetRequestForm instanceof HTMLFormElement) {
        passwordResetRequestForm.reset()
    }
}

const toggleLoadingState = (parentElement) => {
    const loader = parentElement.querySelector('.loader')
    if (!loader) {
        return
    }

    loader.classList.toggle('hidden')

    if (loader.classList.contains('hidden')) {
        loader.ariaHidden = 'true'
    } else {
        loader.ariaHidden = 'false'
    }

    // Disable/enable form submit button to prevent spam clicking
    const button = loader.closest('button')
    if (button) {
        button.disabled = !button.disabled

        const buttonText = button.querySelector('.btn-text')
        if (buttonText) {
            buttonText.classList.toggle('opacity-0')
        }
    }

    // Disable/enable modal close buttons
    const modalCloseButtons = parentElement.closest('.modal')?.querySelectorAll('.close-modal') ?? []
    modalCloseButtons.forEach(button => {
        button.disabled = !button.disabled
    })
}

const showMessage = (formElement, message, success = false) => {
    const messageContainer = formElement.querySelector('output')
    if (messageContainer) {
        messageContainer.classList.add('active')
        messageContainer.innerHTML = message

        if (success) {
            messageContainer.classList.add('success')
        } else {
            messageContainer.classList.remove('success')
        }
    }
}

const clearError = (formElement) => {
    const error = formElement.querySelector("output.active")

    if (error) {
        error.innerHTML = ''
    }
}

const sendPasswordResetRequest = async (event) => {
    const formElement = event.currentTarget
    if (!(formElement instanceof HTMLFormElement)) {
        return
    }

    clearError(formElement)

    const emailField = formElement.querySelector('input[name=password_reset_request_email]')
    if (!(emailField instanceof HTMLInputElement)) {
        return
    }

    const email = emailField.value

    try {
        toggleLoadingState(formElement)

        const response = await fetch('/includes/endpoints/handle-password-reset-request.php', {
            method: 'POST',
            body: JSON.stringify(email)
        })

        const result = await response.json()

        if (result?.status !== 200) {
            const errorMessage = result?.data?.error_message
            showMessage(formElement, errorMessage)
        } else {
            formElement.reset()

            // Dispatch event for modal.js to close programmatically
            const modal = formElement.closest('.modal')
            if (modal?.id) {
                window.dispatchEvent(
                    new CustomEvent('closeModal', {
                        detail: {
                            modalId: modal.id
                        }
                    })
                )
            }

            // Show success toast-notification
            window.dispatchEvent(
                new CustomEvent('showtoast', {
                    detail: {
                        toastId: 'email-sent-toast-notification',
                        message: result.message ?? 'Password reset email sent'
                    }
                })
            )
        }
    } catch (error) {
        console.error(error)
        showMessage(formElement, 'Something went wrong. Please try again later.')
    } finally {
        toggleLoadingState(formElement)
    }
}

document
    .getElementById('password-reset-request-form')
    ?.addEventListener('submit', (event) => {
        event.preventDefault()
        sendPasswordResetRequest(event)
    })

document
    .getElementById('cancel-password-reset')
    ?.addEventListener('click', (event) => {
        cancelPasswordReset(event)
    })