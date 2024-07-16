// SDK/JS/ekiliRelay.js

class EkiliRelay {
    constructor() {
        this.apiUrl = "https://relay.ekilie.com/api/index.php";
        console.log("EkiliRelay connected")
    }

    async sendEmail(to, subject, message, headers = '') {
        const data = {
            to: to,
            subject: subject,
            message: message,
            headers: headers
        };

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            return result;
        } catch (error) {
            return { status: 'error', message: error.message };
        }
    }
}

export default EkiliRelay;
