const db = require("../config/db");

exports.findUserByEmail = (email, callback) => {
    const sql = "SELECT * FROM inv_users WHERE email=?";
    db.query(sql, [email], callback);
};

exports.createUser = (name, email, password, phone, callback) => {
    const sql = "INSERT INTO inv_users (name,email,password,phone) VALUES (?,?,?,?)";
    db.query(sql, [name, email, password, phone], callback);
};