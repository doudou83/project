# Utiliser une image de base Python
FROM python:3.9.0

# Définir le répertoire de travail
WORKDIR /app

# Installer git
RUN apt-get update && apt-get install -y git

# Cloner le dépôt GitHub dans le répertoire de travail
RUN git clone https://github.com/Manuams99/MLProject_titanic .

# Copier le fichier requirements.txt et installer les dépendances
COPY requirements.txt /app/
RUN pip install --upgrade pip
RUN pip install -r requirements.txt 

# Copier le reste de l'application
COPY . /app/

# Changer le répertoire de travail vers /src
WORKDIR /app/src

# Exposer les ports que les applications vont utiliser
EXPOSE 8000 8501

# Définir la commande de lancement de l'application
CMD ["sh", "-c", "uvicorn main:app --host 0.0.0.0 --port 8000 & streamlit run src/frontend.py --server.port 8501"]
