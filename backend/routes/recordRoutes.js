import express from "express";
import { protect } from "../middleware/authMiddleware.js";
import {
  addRecord,
  getRecords,
  getRecordById,
  updateRecord,
} from "../controllers/recordController.js";

const router = express.Router();

// Get All Records
router.get("/", getRecords);

// Get Single Record
router.get("/:id", getRecordById);

// Add Record
router.post("/", protect, addRecord);
// Update Record
router.put(
  "/:id",
  protect,
  updateRecord
);
export default router;