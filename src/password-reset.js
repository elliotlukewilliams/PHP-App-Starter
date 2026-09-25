const toggleLoadingState = (parentElement) => {
    const loader = parentElement.querySelector('.loader')
    if (!loader) return

    loader.classList.toggle('hidden')
    loader.ariaHidden = loader.classList.contains('hidden') ? 'true' : 'false'

    const button = loader.closest('button')
    if (button) {
        button.disabled = !button.disabled

        const buttonText = button.querySelector('.btn-text')
        if (buttonText) {
            buttonText.classList.toggle('opacity-0')
        }
    }
}

const showMessage = (formElement, message, { success = false } = {}) => {
    const container =
        formElement.querySelector('output.message') ||
        formElement.querySelector('output.any-error')

    if (!container) return

    container.classList.add('active')
    container.innerHTML = message
    container.classList.toggle('success', success)
}

const clearMessage = (formElement) => {
    const container = formElement.querySelector('output.active')
    if (!container) return

    container.innerHTML = ''
    container.classList.remove('active', 'success')
}

const postJSON = async (url, data) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })

    return response.json()
}

const resetPassword = async (event) => {
    const formElement = event.currentTarget
    if (!(formElement instanceof HTMLFormElement)) return

    clearMessage(formElement)

    try {
        toggleLoadingState(formElement)

        const formData = new FormData(formElement)
        const requestData = {
            password: formData.get('new_password') ?? '',
            password_confirm: formData.get('new_password_confirm') ?? '',
            unique_token: formData.get('unique_token') ?? ''
        }

        const result = await postJSON(
            '/includes/endpoints/handle-password-reset.php',
            requestData
        )

        if (result?.status !== 200) {
            showMessage(formElement, result?.data?.error_message || 'Something went wrong')
            return
        }

        formElement.reset()

        window.dispatchEvent(
            new CustomEvent('showtoast', {
                detail: {
                    toastId: 'password-reset-toast-notification',
                    message: result.message ?? 'Password reset successful'
                }
            })
        )
    } catch (error) {
        console.error(error)
        showMessage(formElement, 'Something went wrong. Please try again later.')
    } finally {
        toggleLoadingState(formElement)
    }
}

document
    .getElementById('password-reset-form')
    ?.addEventListener('submit', (event) => {
        event.preventDefault()
        resetPassword(event)
    })