const BASE_URL = "http://127.0.0.1:8000/api/v1";

export async function registerUser(payload) {
  const response = await fetch(`${BASE_URL}/auth/register-user`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    throw {
      status: response.status,
      message: result.message || "Registrasi gagal",
      errors: result.errors || null,
    };
  }

  return result;
}

export async function loginUser(payload) {
  const response = await fetch(`${BASE_URL}/auth/login`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    throw {
      status: response.status,
      message: result.message || "Login gagal",
      errors: result.errors || null,
    };
  }

  return result;
}

export async function forgotPassword(payload) {
  const response = await fetch(`${BASE_URL}/auth/forgot-password`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    throw {
      status: response.status,
      message: result.message || "Gagal mengirim link reset password",
      errors: result.errors || null,
    };
  }

  return result;
}

export async function resetPassword(payload) {
  const response = await fetch(`${BASE_URL}/auth/reset-password`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    throw {
      status: response.status,
      message: result.message || "Gagal memperbarui password",
      errors: result.errors || null,
    };
  }

  return result;
}

export async function resendVerification(payload) {
  const response = await fetch(`${BASE_URL}/auth/resend-verification`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    throw {
      status: response.status,
      message: result.message || "Gagal mengirim ulang email verifikasi",
      errors: result.errors || null,
    };
  }

  return result;
}
