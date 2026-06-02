import {
  createContext,
  useContext,
  useEffect,
  useState,
} from "react";

import axios from "axios";

const AuthContext = createContext();

const STORAGE_KEY = "efile-auth";

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  // 👇 ADDED: Loading state to prevent premature routing redirects on refresh
  const [loading, setLoading] = useState(true);

  // Load Stored Auth
  useEffect(() => {
    const storedAuth = localStorage.getItem(STORAGE_KEY);

    if (storedAuth) {
      try {
        const parsed = JSON.parse(storedAuth);
        setUser(parsed.user);
        setToken(parsed.token);
        axios.defaults.headers.common.Authorization = `Bearer ${parsed.token}`;
      } catch (error) {
        console.error("Error parsing auth tokens:", error);
        localStorage.removeItem(STORAGE_KEY);
      }
    }
    // 👇 Auth initialization completed, safe to stop loading
    setLoading(false); 
  }, []);

  // Login
  const login = (userData, jwtToken) => {
    setUser(userData);
    setToken(jwtToken);

    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify({
        user: userData,
        token: jwtToken,
      })
    );

    axios.defaults.headers.common.Authorization = `Bearer ${jwtToken}`;
  };

  // Logout
  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem(STORAGE_KEY);
    delete axios.defaults.headers.common.Authorization;
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        login,
        logout,
        loading, // 👇 Expose loading state to your routes
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);