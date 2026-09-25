const toggleToast = (visible, toastId, message = '') => {
    const toastElement = document.getElementById(toastId)
    if (!toastElement) return

    const toastText = toastElement.querySelector('.toast-text')
    toastElement.classList.toggle('hidden', !visible)

    if (!toastText) return
    if (!visible) {
        toastText.innerHTML = ''
        return
    }
    toastText.innerHTML = ''
    setTimeout(() => {
        toastText.innerHTML = message
    }, 100)
}

// Allow other scripts to trigger toast notifications
window.addEventListener('showtoast', (event) => {
    const { toastId, message } = event.detail || {}

    if (!toastId) return

    toggleToast(true, toastId, message)
})

// Handle toast dismiss buttons
document.querySelectorAll('.toast-dismiss').forEach((el) => {
    el.addEventListener('click', (event) => {
        const button = event.currentTarget

        if (!(button instanceof HTMLElement)) return

        const toastElement = button.closest('.toast-notification')
        if (!toastElement) return

        toggleToast(false, toastElement.id)
    })
})