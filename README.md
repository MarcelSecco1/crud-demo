# 📦 CRUD Demo — GitHub Actions + Docker

Exemplo de aplicação PHP com CRUD completo, deploy automatizado via **GitHub Actions** e **Docker**.

---

## 🗂️ Estrutura do Projeto

``` bash
├── .github/
│   └── workflows/
│       └── deploy.yml        # Pipeline de CI/CD
├── src/
│   ├── api/
│   │   └── users.php         # API REST do CRUD
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   ├── .htaccess             # Rewrite rules do Apache
│   └── index.html            # Frontend
├── Dockerfile
└── README.md
```

---

## 🚀 Rodando Localmente - Teste

```bash
# Build da imagem
docker build -t crud-demo .

# Rodar o container
docker run -d -p 8080:80 --name crud-demo crud-demo

# Acesse: http://localhost:8080
```

---

## ⚙️ Configurando o Deploy Automático

### 1. Secrets do GitHub

Vá em **Settings → Secrets and variables → Actions** e adicione:

| Secret            | Valor                         |
|-------------------|-------------------------------|
| `DOCKER_USERNAME` | Seu usuário no Docker Hub     |
| `DOCKER_PASSWORD` | Senha ou token do Docker Hub  |
| `SERVER_HOST`     | IP ou domínio do seu servidor |
| `SERVER_USER`     | Usuário SSH (ex: `ubuntu`)    |
| `SERVER_SSH_KEY`  | Conteúdo da chave SSH privada |

### 2. Preparando o Servidor

```bash
# Instale o Docker no servidor (Ubuntu)
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
sudo reboot
```

### 3. Deploy

Basta fazer push na branch `main`:

```bash
git add .
git commit -m "Enviando: Nova alteração"
git push origin main
```

O pipeline irá automaticamente:
1. Fazer **build** da imagem Docker
2. Fazer **push** para o Docker Hub
3. Conectar via **SSH** no servidor
4. Parar o container antigo e subir o novo

---

## 🔌 Endpoints da API

| Método | URL                   | Descrição        |
|--------|-----------------------|------------------|
| GET    | `/api/users.php`      | Lista todos      |
| GET    | `/api/users.php?id=1` | Busca por ID     |
| POST   | `/api/users.php`      | Cria usuário     |
| PUT    | `/api/users.php?id=1` | Atualiza usuário |
| DELETE | `/api/users.php?id=1` | Remove usuário   |

**Body (POST/PUT):**
```json
{
        "id": 2,
        "name": "Marcel Secco",
        "email": "marcelsecco1@gmail.com",
        "created_at": "2026-05-01 18:59:30"
    }
```
