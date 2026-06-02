import express from "express";

import cors from "cors";

import dotenv from "dotenv";

import pool from "./config/db.js";

import recordRoutes from "./routes/recordRoutes.js";

import authRoutes from "./routes/authRoutes.js"; 

dotenv.config();

const app = express();

app.use(cors());

app.use(express.json());

// Routes
app.use(
  "/api/records",
  recordRoutes
);
app.use("/api/auth", authRoutes);

// Test Route
app.get("/", async (req, res) => {
  try {
    const result = await pool.query(
      "SELECT NOW()"
    );

    res.json({
      message: "API Running...",
      time: result.rows[0],
    });
  } catch (error) {
    console.log(error);

    res.status(500).json({
      message: "Database Error",
    });
  }
});

const PORT = process.env.PORT || 5000;

app.listen(PORT, () => {
  console.log(
    `Server running on port ${PORT}`
  );
});