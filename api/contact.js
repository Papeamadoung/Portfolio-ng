const MAX_LENGTHS = {
  name: 100,
  email: 254,
  subject: 150,
  message: 5000,
};

function isValid(value, maxLength) {
  return typeof value === 'string' && value.trim().length > 0 && value.trim().length <= maxLength;
}

function getRequestData(request) {
  if (!request.body) {
    return {};
  }

  if (typeof request.body === 'string') {
    return Object.fromEntries(new URLSearchParams(request.body));
  }

  return request.body;
}

export default async function handler(request, response) {
  if (request.method !== 'POST') {
    response.setHeader('Allow', 'POST');
    return response.status(405).send('Method Not Allowed');
  }

  const { name = '', email = '', subject = '', message = '', website = '' } = getRequestData(request);
  const cleanName = String(name).trim();
  const cleanEmail = String(email).trim();
  const cleanSubject = String(subject).trim();
  const cleanMessage = String(message).trim();

  if (website || !isValid(cleanName, MAX_LENGTHS.name) || !isValid(cleanEmail, MAX_LENGTHS.email)
    || !isValid(cleanSubject, MAX_LENGTHS.subject) || !isValid(cleanMessage, MAX_LENGTHS.message)
    || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail)) {
    return response.status(400).send('Veuillez remplir correctement tous les champs.');
  }

  const { RESEND_API_KEY, CONTACT_FROM_EMAIL, CONTACT_TO_EMAIL } = process.env;
  if (!RESEND_API_KEY || !CONTACT_FROM_EMAIL || !CONTACT_TO_EMAIL) {
    return response.status(503).send('Le formulaire nâ€™est pas encore configurÃ©.');
  }

  try {
    const emailResponse = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${RESEND_API_KEY}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from: CONTACT_FROM_EMAIL,
        to: [CONTACT_TO_EMAIL],
        reply_to: cleanEmail,
        subject: cleanSubject,
        text: `Nom : ${cleanName}\nEmail : ${cleanEmail}\n\n${cleanMessage}`,
      }),
    });

    if (!emailResponse.ok) {
      throw new Error(`Resend a rÃ©pondu avec le statut ${emailResponse.status}`);
    }

    return response.status(200).send('OK');
  } catch (error) {
    console.error('Erreur lors de lâ€™envoi du formulaire :', error);
    return response.status(500).send('Le message nâ€™a pas pu Ãªtre envoyÃ© pour le moment.');
  }
}

