# SLiMS Storage Monitor Plugin

> ⚠️ **Disclaimer**  
> JANGAN langsung pasang DI SLiMS Operasional (tes di PC/SLiMS lain). Gunakan dengan risiko Anda sendiri.


The SLiMS Storage Monitor is a plugin for the Senayan Library Management System (SLiMS) designed to provide real-time reports on disk usage for important SLiMS folders. It helps administrators understand storage consumption, identify large files or plugins, and estimate overall server disk space.
![msedge_MgJ2zR8uxQ](https://github.com/user-attachments/assets/dae8b376-2f39-4c6e-aa8e-78c62985f86c)

## Features

*   **Real-time Disk Usage:** Reports current disk usage for configured SLiMS folders.
*   **Summary View:**
    *   Total size and item count for each monitored folder.
    *   Breakdown of top file types by count within each folder.
    *   Overall total for all monitored SLiMS data (files and size).
*   **Detailed Folder View:**
    *   Lists all files and subfolders within a selected monitored path.
    *   Displays individual sizes for each item.
    *   Paginated results for large folders.
*   **Top Items Display:** Shows the top 5 largest files (or plugins, in the case of the `plugins` directory) for each monitored path in a card layout on the summary page.
*   **Server Disk Space Estimation:**
    *   Estimates total, used, and free disk space for the partition where SLiMS is installed.
    *   Visual progress bar indicating usage percentage.
*   **Configurable Paths:**
    *   Monitors a default set of SLiMS folders (`repository`, `files/backup`, `files/reports`, `images/*`, `plugins`). Paths are derived relative to the SLiMS installation root.
    *   Allows overriding default paths or adding new custom paths via SLiMS system configuration (`$sysconf['folder_size_report_paths']`).
*   **File Filtering:**
    *   Ignores common system/web files (e.g., `index.php`, `.htaccess`, `.php`, `.env`, `.sh`) by default to provide more relevant statistics.
    *   Allows specific folders (e.g., `files/reports`) to keep HTML files in the scan.
*   **User-Friendly Interface:**
    *   Integrated into the SLiMS admin panel under "Reporting" as "Storage Report".
    *   Uses Bootstrap styling and FontAwesome icons for a clean and modern look.
    *   Provides easy navigation between summary and detail views.

## Installation

read here [https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md](https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md)

## Usage

1.  Navigate to **Reporting > Storage Monitor** in the SLiMS admin panel.
2.  The **Summary Page** will display:
    *   Server Disk Space Estimation (for the SLiMS partition).
    *   A table summarizing each monitored folder: name, path, item count, total size, and top file types.
    *   A section with cards, each showing the top 5 largest files/folders for the respective monitored paths.
3.  Click on a folder name in the summary table to view the **Detailed Page** for that folder. This page lists all contents (files and sub-folders) with their sizes, sorted by size, and includes pagination if there are many items.
4.  Use the "Refresh" button to get the latest data.
5.  Use the "Back to Summary" button from the detail page to return to the main report.
