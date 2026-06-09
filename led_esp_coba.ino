#include <WiFi.h>
#include <HTTPClient.h>

// Konfigurasi Wi-Fi
const char* ssid     = "Direktur Keuangan dan Fasilitas";
const char* password = "harkatnegeri";

// URL Web API (Ganti dengan IP Laptop/Server Anda yang menjalankan XAMPP)
// Contoh jika pakai XAMPP lokal: "http://192.168.147.132/iot_led/index.php?get_status=1"
const char* serverUrl = "http://192.168.147.132/iot_led/index.php?get_status=1";

const int ledPin = 2; // LED bawaan ESP32

void setup() {
  Serial.begin(115200);
  pinMode(ledPin, OUTPUT);

  // Proses Menghubungkan ke Wi-Fi
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWi-Fi Terhubung!");
  Serial.print("IP ESP32: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Pastikan Wi-Fi tetap terhubung sebelum melakukan request
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    // Inisialisasi HTTP request ke server
    http.begin(serverUrl);
    
    // Melakukan GET request
    int httpResponseCode = http.GET();
    
    if (httpResponseCode > 0) {
      // Mengambil data string balasan dari server (akan berisi "0" atau "1")
      String payload = http.getString();
      payload.trim(); // Menghapus spasi tak terlihat jika ada
      
      Serial.print("Respon Server: ");
      Serial.println(payload);

      // Eksekusi status LED berdasarkan database
      if (payload == "1") {
        digitalWrite(ledPin, HIGH);
        Serial.println("Status: LED NYALA");
      } else if (payload == "0") {
        digitalWrite(ledPin, LOW);
        Serial.println("Status: LED MATI");
      }
    } else {
      Serial.print("Error saat GET Request: ");
      Serial.println(httpResponseCode);
    }
    
    // Menutup koneksi HTTP
    http.end();
  } else {
    Serial.println("Wi-Fi Terputus!");
  }

  // Jeda waktu pengecekan ke database (misal: setiap 2 detik sekali)
  delay(2000);
}
