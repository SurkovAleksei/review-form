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
            } else if (value.trim().length > 255) {
                errors.email = 'Максимум 255 символов';
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
                } else if (value.trim().length > 20) {
                    errors.phone = 'Максимум 20 символов';
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