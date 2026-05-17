module.exports = {
  content: [
    "./app/views/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        app: {
          bg: "#f5e8d9",
          card: "#faf5ef",
          input: "#ffffff",
          text: "#111827",
          muted: "#b79c8b",
          white: "#ffffff",
          primary: "#6d4c5b",
          secondary: "#7b5c69",
          success: "#16a34a",
          warning: "#eab308",
          danger: "#b94b4b",
          border: "#ead8c7",
          focus: "#c8b1a1",
          surface: "#eddecc",
          sidebar: "#f5e8d9",
          "sidebar-hover": "#eddecc",
          "sidebar-active": "#ead8c7",
          soft: "#faf5ef",
          strong: "#6d4c5b",
          ring: "#e8d7ca",
          accent: "#7b5c69",
          "header-muted": "#b79c8b",
          "danger-soft": "#f9dede",
          "panel": "#ead8c7",
          "panel-border": "#eddecc",
          "keycap": "#faf5ef",
        },
      },
      fontFamily: {
        body: ["Poppins", "sans-serif"],
        ui: ["Poppins", "sans-serif"],
      },
      borderRadius: {
        card: "0.75rem",
        field: "0.5rem",
      },
      boxShadow: {
        card: "0 20px 40px rgba(15, 23, 42, 0.08)",
        panel: "0 18px 45px rgba(15, 23, 42, 0.06)",
      },
      spacing: {
        "card-x": "3.5rem",
        "card-y": "2.5rem",
        field: "3rem",
        eye: "15px",
        sidebar: "17.5rem",
        topbar: "5rem",
      },
      fontSize: {
        helper: "13px",
      },
    },
  },
};
