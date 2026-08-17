/**
 * BamBam Cat Hotel - Booking Management System
 * Version: 1.0.0
 * Architecture: Object-Oriented (ES6 Classes)
 */

// ==========================================
// 1. Notification System (ระบบแจ้งเตือนแบบแยกส่วน)
// ==========================================
class NotificationSystem {
  static show(message, type = 'info') {
    // ป้องกันการแจ้งเตือนซ้อนกัน
    const existingToast = document.getElementById('pro-toast');
    if (existingToast) existingToast.remove();

    const toast = document.createElement('div');
    toast.id = 'pro-toast';
    
    // ตั้งค่าสีและไอคอน (รองรับ FontAwesome)
    const themes = {
      success: { bg: '#52895A', icon: '<i class="fa-solid fa-circle-check"></i>' }, // เขียวธีมเว็บ
      error: { bg: '#D35C5C', icon: '<i class="fa-solid fa-circle-xmark"></i>' },   // แดงธีมเว็บ
      warning: { bg: '#D9A036', icon: '<i class="fa-solid fa-triangle-exclamation"></i>' }, // เหลืองธีมเว็บ
      info: { bg: '#E58A35', icon: '<i class="fa-solid fa-circle-info"></i>' } // ส้มหลัก
    };

    const theme = themes[type] || themes.info;

    toast.style.cssText = `
      position: fixed;
      top: -100px;
      left: 50%;
      transform: translateX(-50%);
      background: ${theme.bg};
      color: #ffffff;
      padding: 14px 28px;
      border-radius: 999px;
      font-family: inherit;
      font-size: 0.95rem;
      font-weight: 500;
      box-shadow: 0 10px 30px rgba(74, 59, 50, 0.2);
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 12px;
      opacity: 0;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    `;

    toast.innerHTML = `<span style="font-size: 1.2rem;">${theme.icon}</span> <span>${message}</span>`;
    document.body.appendChild(toast);

    // Force DOM reflow เพื่อให้ Animation ทำงานสมูท
    void toast.offsetWidth; 

    // Animate In
    toast.style.top = '40px';
    toast.style.opacity = '1';

    // Auto Dismiss (ลบตัวเองออกหลัง 4 วินาที)
    setTimeout(() => {
      toast.style.top = '-100px';
      toast.style.opacity = '0';
      toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
  }
}

// ==========================================
// 2. Booking Manager (ระบบคำนวณและจัดการฟอร์ม)
// ==========================================
class BookingManager {
  constructor() {
    // ผูก Element เข้ากับตัวแปรใน Class
    this.form = document.getElementById('booking-form');
    this.inputs = {
      room: document.getElementById('roomtype'),
      start: document.getElementById('start_date'),
      end: document.getElementById('end_date')
    };
    this.displays = {
      roomName: document.getElementById('sum-room'),
      nights: document.getElementById('sum-nights'),
      total: document.getElementById('sum-total')
    };
    
    // ตั้งค่าคงที่ (Constants)
    this.MS_PER_DAY = 1000 * 60 * 60 * 24;
    
    // ตัวจัดรูปแบบเงินบาทไทยแบบ Native API
    this.currencyFormatter = new Intl.NumberFormat('th-TH', { 
      style: 'currency', 
      currency: 'THB' 
    });

    this.init();
  }

  init() {
    if (!this.form) return; // Guard Clause ถ้าหน้าไหนไม่มีฟอร์มให้หยุดทำงานทันที
    this.bindEvents();
  }

  bindEvents() {
    // ผูก Event ให้กับ Input ทุกตัวแบบ Loop 
    Object.values(this.inputs).forEach(input => {
      if (input) {
        input.addEventListener('change', () => this.handleCalculate());
      }
    });

    // ผูก Event Submit ฟอร์ม
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));
  }

  // ดึงข้อมูลและคำนวณ State ทั้งหมด
  getState() {
    const { room, start, end } = this.inputs;
    if (!room || !start || !end) return null;

    const selectedOption = room.options[room.selectedIndex];
    const pricePerNight = parseFloat(selectedOption?.dataset?.price || 0);
    const startDate = new Date(start.value);
    const endDate = new Date(end.value);

    let nights = 0;
    let totalPrice = 0;
    let isValidDate = false;

    // เช็คความถูกต้องของวันที่ ป้องกันบัคเวลาผู้ใช้กดมั่ว
    if (start.value && end.value && !isNaN(startDate) && !isNaN(endDate)) {
      if (endDate > startDate) {
        nights = Math.ceil((endDate - startDate) / this.MS_PER_DAY);
        totalPrice = nights * pricePerNight;
        isValidDate = true;
      }
    }

    return {
      roomName: selectedOption?.value ? selectedOption.text : '-',
      nights,
      totalPrice,
      isValidDate
    };
  }

  handleCalculate() {
    try {
      const state = this.getState();
      if (state) this.renderUI(state);
    } catch (error) {
      console.error('Calculation Error:', error); // ซ่อน Error ไว้หลังบ้าน
    }
  }

  // อัปเดตหน้าจอแสดงผล
  renderUI(state) {
    if (this.displays.roomName) {
      this.displays.roomName.textContent = state.roomName;
    }
    
    if (this.displays.nights) {
      this.displays.nights.textContent = state.nights > 0 ? `${state.nights} คืน` : '-';
    }

    if (this.displays.total) {
      const formattedPrice = state.totalPrice > 0 ? this.currencyFormatter.format(state.totalPrice) : '฿0.00';
      
      // ถ้าตัวเลขเปลี่ยน ให้เล่น Animation เด้งดึ๋งเล็กน้อย
      if (this.displays.total.textContent !== formattedPrice) {
        this.displays.total.textContent = formattedPrice;
        this.animateElement(this.displays.total);
      }
    }
  }

  // Effect พิเศษเวลาตัวเลขเปลี่ยน (ใช้ requestAnimationFrame เพื่อประสิทธิภาพสูงสุด)
  animateElement(el) {
    requestAnimationFrame(() => {
      el.style.transition = 'transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
      el.style.transform = 'scale(1.1)';
      setTimeout(() => el.style.transform = 'scale(1)', 200);
    });
  }

  // ระบบจำลองการยิง API ด้วย Promise (Modern Async/Await)
  mockApiRequest(delay = 1500) {
    return new Promise(resolve => setTimeout(resolve, delay));
  }

  // จัดการฟอร์มเวลาผู้ใช้กด "ยืนยันการจอง"
  async handleSubmit(e) {
    e.preventDefault();

    const state = this.getState();
    const submitBtn = this.form.querySelector('button[type="submit"]') || this.form.querySelector('button');
    
    // --- Validation (ตรวจสอบข้อมูล) ---
    if (!this.inputs.room.value) {
      return NotificationSystem.show('กรุณาเลือกประเภทห้องพัก', 'warning');
    }

    if (!state || !state.isValidDate) {
      return NotificationSystem.show('กรุณาระบุวันที่เข้าพักให้ถูกต้อง (วันรับกลับต้องหลังวันเข้าพัก)', 'error');
    }

    // --- Processing (เตรียมโหลดข้อมูล) ---
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    
    try {
      if (submitBtn) {
        // เปลี่ยนข้อความปุ่มและใส่ไอคอนโหลด
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังดำเนินการ...';
        submitBtn.disabled = true; 
        submitBtn.style.opacity = '0.8';
      }

      // จำลองการเชื่อมต่อฐานข้อมูล
      await this.mockApiRequest(1800);
      
      // ถ้าสำเร็จ
      NotificationSystem.show('ยืนยันการจองสำเร็จ! ระบบกำลังพาท่านไปหน้าชำระเงินมัดจำ', 'success');
      
      // ตัวอย่างคำสั่งเปลี่ยนหน้าเว็บ:
      // setTimeout(() => window.location.href = '/payment-gateway', 2000);
      
    } catch (error) {
      NotificationSystem.show('เกิดข้อผิดพลาดจากเซิร์ฟเวอร์ กรุณาลองใหม่อีกครั้ง', 'error');
    } finally {
      // คืนค่าปุ่มกลับเป็นเหมือนเดิมเสมอ ไม่ว่าจะทำงานผ่านหรือไม่ผ่าน
      if (submitBtn) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
      }
    }
  }
}

// ==========================================
// 3. Initialize App (เริ่มการทำงานเมื่อโหลดเว็บเสร็จ)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  new BookingManager(); // เรียกใช้ Class แค่บรรทัดเดียว จบ!
});