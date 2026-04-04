const bcrypt = require("bcryptjs");
const userModel = require("../models/userModel");
const generateToken = require("../utils/generateToken");

exports.signup = async (req, res) => {

    const { name, email, phone, password } = req.body;


    userModel.findUserByEmail(email, (err, result) => {

        if (result.length > 0) {
            return res.json({ message: "User already exists" });
        }

        userModel.createUser(name, email, password, phone, (err) => {

            if (err) {
                return res.status(500).json({ message: "Error creating user" });
            }

            res.json({
                message: "Signup successful"
            });

        });

    });

};

exports.login = (req, res) => {

    const { email, password } = req.body;

    userModel.findUserByEmail(email, async (err, result) => {

        if (result.length === 0) {
            return res.json({ message: "User not found" });
        }

        const user = result[0];

        const match = password === user.password;

        if (!match) {
            return res.json({ message: "Invalid password" });
        }

        const token = generateToken(user.id);

        res.json({
            message: "Login successful",
            token: token
        });

    });

};