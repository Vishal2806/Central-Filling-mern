import axios from "axios";

const API =
  "http://localhost:5000/api/auth";

// Login API
export const loginApi =
  async (loginData) => {
    const response =
      await axios.post(
        `${API}/login`,
        loginData
      );

    return response.data;
  };