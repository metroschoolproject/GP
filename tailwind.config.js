module.exports = {
  content: [
    "./app/views/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        app: {
          bg: "#e5e7eb",
          card: "#f3f4f6",
          input: "#ffffff",
          text: "#155e75",
          muted: "#6b7280",
          white: "#ffffff",
          primary: "#155e75",
          secondary: "#1e40af",
          success: "#16a34a",
          warning: "#eab308",
          danger: "#dc2626",
          border: "#d1d5db",
          focus: "#a5f3fc",
        },
      },
      fontFamily: {
        body: ["Playfair Display", "serif"],
        ui: ["Poppins", "sans-serif"],
      },
      borderRadius: {
        card: "0.75rem",
        field: "0.5rem",
      },
      boxShadow: {
        card: "0 20px 40px rgba(15, 23, 42, 0.08)",
      },
      spacing: {
        "card-x": "3.5rem",
        "card-y": "2.5rem",
        field: "3rem",
        eye: "15px",
      },
      fontSize: {
        helper: "13px",
      },
    },
  },
};
