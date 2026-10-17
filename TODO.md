# VPS panel outcomes

- [ ] **Authenticated VPS management:** Existing login and registration routes remain available; signed-in users can reach a VPS area with controls to create a VPS, select Ubuntu or Debian, install the selected OS, and view VPS state.
- [ ] **Lifecycle and SSHX:** A VPS interface provides start, stop, restart, and reinstall controls and exposes SSHX access only when an actual host integration can issue a session link; do not show a fabricated link or claim host actions completed.
- [ ] **Admin resource controls:** An admin-only area exposes per-user RAM, CPU, and disk limits and whether a user may set resources themselves; authorization remains admin-only.
- [ ] **Transparent LXD boundary:** The panel identifies the LXD/SSHX host integration status and does not reuse unsafe privileged host configuration from the supplied Discord bot.
- [ ] **Responsive glass UI:** VPS views use a transparent glass panel visual treatment and remain usable on narrow screens.
