import pool from "../config/db.js";

import bcrypt from "bcryptjs";

import jwt from "jsonwebtoken";

// Login
export const loginUser = async (
  req,
  res
) => {
  try {
    const { email, password } =
      req.body;

    // Check User
    const userResult =
      await pool.query(
        `
        SELECT *
        FROM users
        WHERE email = $1
      `,
        [email]
      );

    if (
      userResult.rows.length === 0
    ) {
      return res.status(400).json({
        success: false,
        message: "Invalid email",
      });
    }

    const user =
      userResult.rows[0];

    // Check Password
    const isMatch =
      await bcrypt.compare(
        password,
        user.password
      );

    if (!isMatch) {
      return res.status(400).json({
        success: false,
        message:
          "Invalid password",
      });
    }

    // Create JWT
    const token = jwt.sign(
      {
        id: user.id,
        role: user.role,
      },
      "SECRET_KEY",
      {
        expiresIn: "7d",
      }
    );

    res.status(200).json({
      success: true,

      token,

      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
      },
    });
  } catch (error) {
    console.log(error);

    res.status(500).json({
      success: false,
      message: "Server Error",
    });
  }
};