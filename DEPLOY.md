bash deploy.sh              # Deploy bình thường (auto backup trước)
bash deploy.sh --build      # Full rebuild (auto backup trước)  
bash deploy.sh --backup     # Chỉ backup database
bash deploy.sh --restore    # Restore từ backup gần nhất
bash deploy.sh --fix-mysql  # Sửa MySQL password AN TOÀN (không mất data)
bash deploy.sh --status     # Kiểm tra trạng thái