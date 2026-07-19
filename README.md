# 🍋 LEMON Launcher

<img src="https://github.com/PojavLauncherTeam/PojavLauncher/blob/v3_openjdk/app_pojavlauncher/src/main/assets/pojavlauncher.png" align="left" width="130" height="150" alt="LEMON Launcher logo">

[![LEMON Launcher Build](https://github.com/phanmaax84/lemon-launcher/workflows/LEMON%20Launcher%20Build/badge.svg)](https://github.com/phanmaax84/lemon-launcher/actions)

*LEMON Launcher is a fork of PojavLauncher with a fresh yellow-black theme and built-in Modrinth support!*

## Features

🍋 **Fresh Theme** - Yellow and black color scheme  
📦 **Built-in Modrinth** - Browse and download mods directly from Modrinth  
🎮 **Full Minecraft Support** - Play Minecraft Java Edition on Android

## Building

### Using GitHub Actions

The APK is automatically built when you push to this repository. Download the built APK from the Actions tab.

### Manual Build

```bash
git clone https://github.com/phanmaax84/lemon-launcher.git
cd lemon-launcher
./gradlew :app_pojavlauncher:assembleDebug
```

The built APK will be located in `app_pojavlauncher/build/outputs/apk/debug/`.

## Based on PojavLauncher

LEMON Launcher is based on [PojavLauncher](https://github.com/PojavLauncherTeam/PojavLauncher) - the popular Minecraft Java Edition launcher for Android.

## License

Licensed under [GNU LGPLv3](LICENSE)
