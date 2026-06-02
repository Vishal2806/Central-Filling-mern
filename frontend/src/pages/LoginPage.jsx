import { useState } from "react";

import { useNavigate } from "react-router-dom";

import { loginApi } from "../api/authApi";

import { useAuth } from "../context/AuthContext";

const LoginPage = () => {
  const navigate = useNavigate();

  const { login } = useAuth();

  const [formData, setFormData] =
    useState({
      email: "",
      password: "",
    });

  const [loading, setLoading] =
    useState(false);

  // Handle Change
  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]:
        e.target.value,
    });
  };

  // Submit
  const handleSubmit = async (
    e
  ) => {
    e.preventDefault();

    try {
      setLoading(true);

      const response =
        await loginApi(formData);

      if (response.success) {
        login(
          response.user,
          response.token
        );

        navigate("/");
      }
    } catch (error) {
      console.log(error);

      alert(
        error.response?.data
          ?.message ||
          "Login Failed"
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f4f7fb] flex items-center justify-center px-4">
      <div className="bg-white w-full max-w-md rounded-2xl shadow-lg border border-gray-200 p-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold text-[#1f3a56]">
            Court E-Filing
          </h1>

          <p className="text-gray-500 mt-2">
            Registry Management System
          </p>
        </div>

        <form
          onSubmit={handleSubmit}
          className="mt-8 space-y-5"
        >
          {/* Email */}
          <div>
            <label className="block text-sm font-semibold text-gray-600 mb-2">
              Email
            </label>

            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              placeholder="Enter email"
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* Password */}
          <div>
            <label className="block text-sm font-semibold text-gray-600 mb-2">
              Password
            </label>

            <input
              type="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              placeholder="Enter password"
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* Button */}
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-[#1f3a56] hover:bg-[#163047] text-white py-3 rounded-xl font-semibold transition"
          >
            {loading
              ? "Logging in..."
              : "Login"}
          </button>
        </form>

        {/* Demo */}
        <div className="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
          <p className="text-sm font-semibold text-gray-700">
            Demo Credentials
          </p>

          <p className="text-sm text-gray-600 mt-2">
            admin@test.com
          </p>

          <p className="text-sm text-gray-600">
            123456
          </p>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;