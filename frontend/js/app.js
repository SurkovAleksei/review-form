const {
    createApp,
    ref,
    reactive,
    computed
} = Vue;

createApp({
    setup() {
        const form = reactive({
            name: '',
            email: '',
            phone: '',
            comment: ''
        });

        const errors = reactive({
            name: '',
            email: '',
            phone: '',
            comment: ''
        });

        const loading = ref(false);
        const submitted = ref(false);
        const checking = ref(false);
        const serverResult = ref(null);
        const error = ref(null);

        const commentLength = computed(() => form.comment.length);

        function validateField(field) {
            const value = form[field];

            switch (field) {
                case 'name':
                    if (!value.trim()) {
                        errors.name = 'Имя обязательно';
                    } else if (value.trim().length < 2) {
                        errors.name = 'Минимум 2 символа';
                    } else if (value.trim().length > 100) {
                        errors.name = 'Максимум 100 символов';
                    } else if (!/^[a-zA-Zа-яА-Я\s\-]+$/.test(value.trim())) {
                        errors.name = 'Только буквы и пробелы';
                    } else {
                        errors.name = '';
                    }
                    break;

                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!value.trim()) {
                        errors.email = 'Email обязателен';
                    } else if (!emailRegex.test(value.trim())) {
                        errors.email = 'Введите корректный email';
                    } else {
                        errors.email = '';
                    }
                    break;

                case 'phone':
                    if (!value.trim()) {
                        errors.phone = 'Телефон обязателен';
                    } else {
                        const digits = value.replace(/[^0-9+]/g, '');
                        if (digits.length < 10 || digits.length > 15) {
                            errors.phone = 'Введите 10-15 цифр';
                        } else {
                            errors.phone = '';
                        }
                    }
                    break;

                case 'comment':
                    if (!value.trim()) {
                        errors.comment = 'Сообщение обязательно';
                    } else if (value.trim().length < 10) {
                        errors.comment = 'Минимум 10 символов';
                    } else if (value.trim().length > 5000) {
                        errors.comment = 'Максимум 5000 символов';
                    } else {
                        errors.comment = '';
                    }
                    break;
            }
        }

        function validateAll() {
            ['name', 'email', 'phone', 'comment'].forEach(f => validateField(f));
        }

        function isValid() {
            return !errors.name && !errors.email && !errors.phone && !errors.comment &&
                form.name && form.email && form.phone && form.comment;
        }

        async function checkServer() {
            checking.value = true;
            serverResult.value = null;

            try {
                const r = await fetch('http://localhost:8000/api/health');
                const data = await r.json();
                serverResult.value = {
                    ok: r.ok,
                    msg: r.ok ? 'Сервер работает: ' + JSON.stringify(data) : 'Ошибка: ' + r.status
                };
            } catch (e) {
                serverResult.value = {
                    ok: false,
                    msg: 'Сервер не отвечает: ' + e.message
                };
            }
            checking.value = false;
        }

        async function submitForm() {
            validateAll();

            if (!isValid()) {
                const firstError = document.querySelector('.error');
                if (firstError) firstError.focus();
                return;
            }

            loading.value = true;
            error.value = null;
            serverResult.value = null;

            const data = {
                name: form.name.trim(),
                email: form.email.trim().toLowerCase(),
                phone: form.phone.trim(),
                comment: form.comment.trim()
            };

            try {
                const response = await fetch('http://localhost:8000/api/contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    submitted.value = true;
                } else {
                    error.value = {
                        message: result.message || 'Ошибка отправки',
                        details: JSON.stringify(result, null, 2)
                    };
                    if (response.status === 422) {
                        const firstError = Object.values(result.errors || {})[0];
                        error.value.message = Array.isArray(firstError) ? firstError[0] : 'Проверьте поля';
                    } else if (response.status === 429) {
                        error.value.message = 'Слишком много запросов. Попробуйте позже.';
                    }
                }
            } catch (e) {
                error.value = {
                    message: 'Нет соединения с сервером',
                    details: e.message
                };
            } finally {
                loading.value = false;
            }
        }

        function resetForm() {
            form.name = '';
            form.email = '';
            form.phone = '';
            form.comment = '';

            Object.keys(errors).forEach(key => errors[key] = '');

            submitted.value = false;
            error.value = null;
            serverResult.value = null;
        }

        return {
            form,
            errors,
            loading,
            submitted,
            checking,
            serverResult,
            error,
            commentLength,
            validateField,
            submitForm,
            checkServer,
            resetForm,
            isValid
        };
    }
}).mount('#app');