import React from "react";

import ReactDOM from "react-dom/client";

import App from "./App";

import "./index.css";

import { AuthProvider } from "./context/AuthContext";

import { RecordsProvider } from "./context/RecordsContext";

ReactDOM.createRoot(
  document.getElementById("root")
).render(
  <React.StrictMode>
    <AuthProvider>
      <RecordsProvider>
        <App />
      </RecordsProvider>
    </AuthProvider>
  </React.StrictMode>
);