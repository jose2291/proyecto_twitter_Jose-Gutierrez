--Tabla Usuarios

CREATE TABLE Usuarios (
    usuario_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR2(50) NOT NULL,
    apellido VARCHAR2(50) NOT NULL,
    email VARCHAR2(100) UNIQUE NOT NULL,
    telefono VARCHAR2(15),
    sexo VARCHAR2(10),
    nombre_usuario VARCHAR2(50) UNIQUE NOT NULL,
    contraseña VARCHAR2(255) NOT NULL,
    fecha_registro DATE DEFAULT SYSDATE,
    biografia VARCHAR2(255),
    sitio_web VARCHAR2(100),
    ubicacion VARCHAR2(100),
    fecha_nacimiento DATE,
    foto_perfil BLOB, -- Imagen de perfil
    banner_perfil BLOB -- Imagen de banner en el perfil
);

--Tabla Publicaciones


CREATE TABLE Publicaciones (
    publicacion_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    contenido VARCHAR2(280) NOT NULL,
    fecha_publicacion DATE DEFAULT SYSDATE,
    imagen_publicacion BLOB, -- Imagen asociada a la publicación
    video_publicacion BLOB, -- Video asociado a la publicación
    archivo_adjunto BLOB -- Archivo adjunto a la publicación
);

--Tabla Comentarios

CREATE TABLE Comentarios (
    comentario_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    publicacion_id NUMBER NOT NULL REFERENCES Publicaciones(publicacion_id),
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    contenido VARCHAR2(280) NOT NULL,
    fecha_comentario DATE DEFAULT SYSDATE,
    imagen_comentario BLOB -- Imagen asociada al comentario
);

--Tabla Mensajes_Privados

CREATE TABLE Mensajes_Privados (
    mensaje_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    emisor_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    receptor_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    contenido VARCHAR2(1000) NOT NULL,
    fecha_envio DATE DEFAULT SYSDATE,
    imagen_mensaje BLOB, -- Imagen asociada al mensaje privado
    archivo_adjunto BLOB -- Archivo adjunto al mensaje privado
);

--Tabla Likes

CREATE TABLE Likes (
    like_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    publicacion_id NUMBER NOT NULL REFERENCES Publicaciones(publicacion_id),
    fecha_like DATE DEFAULT SYSDATE
);

--Tabla Retweets

CREATE TABLE Retweets (
    retweet_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    publicacion_id NUMBER NOT NULL REFERENCES Publicaciones(publicacion_id),
    fecha_retweet DATE DEFAULT SYSDATE
);

--Tabla Seguidores

CREATE TABLE Seguidores (
    seguidor_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    seguidor_usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    fecha_seguimiento DATE DEFAULT SYSDATE,
    UNIQUE (usuario_id, seguidor_usuario_id) -- Para evitar duplicados
);

--Tabla Notificaciones

CREATE TABLE Notificaciones (
    notificacion_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    tipo VARCHAR2(50) NOT NULL,
    mensaje VARCHAR2(255) NOT NULL,
    fecha_notificacion DATE DEFAULT SYSDATE,
    leido NUMBER(1) DEFAULT 0 -- 0: No leído, 1: Leído
);

--Tabla Hashtags

CREATE TABLE Hashtags (
    hashtag_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR2(100) NOT NULL,
    publicacion_id NUMBER NOT NULL REFERENCES Publicaciones(publicacion_id)
);

--Tabla Bloqueos

CREATE TABLE Bloqueos (
    bloqueo_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    usuario_bloqueado_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    fecha_bloqueo DATE DEFAULT SYSDATE,
    UNIQUE (usuario_id, usuario_bloqueado_id) -- Para evitar duplicados
);

--Tabla Listas

CREATE TABLE Listas (
    lista_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    nombre VARCHAR2(100) NOT NULL,
    descripcion VARCHAR2(255),
    fecha_creacion DATE DEFAULT SYSDATE,
    imagen_lista BLOB -- Imagen asociada a la lista
);

--Tabla Miembros_Lista

CREATE TABLE Miembros_Lista (
    miembro_lista_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    lista_id NUMBER NOT NULL REFERENCES Listas(lista_id),
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    fecha_agregado DATE DEFAULT SYSDATE,
    UNIQUE (lista_id, usuario_id) -- Para evitar duplicados
);

--Tabla Eventos

CREATE TABLE Eventos (
    evento_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    creador_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    nombre VARCHAR2(100) NOT NULL,
    descripcion VARCHAR2(255),
    fecha_evento DATE,
    ubicacion VARCHAR2(100),
    imagen_evento BLOB -- Imagen asociada al evento
);

--Tabla Participantes_Eventos

CREATE TABLE Participantes_Evento (
    participante_evento_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    evento_id NUMBER NOT NULL REFERENCES Eventos(evento_id),
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    fecha_participacion DATE DEFAULT SYSDATE,
    UNIQUE (evento_id, usuario_id) -- Para evitar duplicados
);


--Tabla Configuraciones_Usuario

CREATE TABLE Configuraciones_Usuario (
    configuracion_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    configuracion JSON NOT NULL, -- Datos de configuración en formato JSON
    fecha_modificacion DATE DEFAULT SYSDATE
);


--Tabla Reportes

CREATE TABLE Reportes (
    reporte_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    reportador_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    publicacion_id NUMBER REFERENCES Publicaciones(publicacion_id),
    comentario_id NUMBER REFERENCES Comentarios(comentario_id),
    motivo VARCHAR2(255) NOT NULL,
    fecha_reporte DATE DEFAULT SYSDATE
);


--Tabla Estadisticas_Usuario

CREATE TABLE Estadisticas_Usuario (
    estadistica_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    total_publicaciones NUMBER DEFAULT 0,
    total_likes NUMBER DEFAULT 0,
    total_seguidores NUMBER DEFAULT 0,
    total_seguidos NUMBER DEFAULT 0,
    fecha_ultima_actualizacion DATE DEFAULT SYSDATE
);


--Tabla Favoritos

CREATE TABLE Favoritos (
    favorito_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    publicacion_id NUMBER NOT NULL REFERENCES Publicaciones(publicacion_id),
    fecha_favorito DATE DEFAULT SYSDATE,
    UNIQUE (usuario_id, publicacion_id) -- Para evitar duplicados
);

--Tabla Encuestas

CREATE TABLE Encuestas (
    encuesta_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    creador_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    pregunta VARCHAR2(255) NOT NULL,
    opciones JSON NOT NULL, -- Opciones de la encuesta en formato JSON
    fecha_creacion DATE DEFAULT SYSDATE,
    fecha_cierre DATE
);

--Tabla Votos_Encuesta

CREATE TABLE Votos_Encuesta (
    voto_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    encuesta_id NUMBER NOT NULL REFERENCES Encuestas(encuesta_id),
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    opcion_seleccionada VARCHAR2(255) NOT NULL,
    fecha_voto DATE DEFAULT SYSDATE,
    UNIQUE (encuesta_id, usuario_id) -- Para evitar duplicados
);

--Tabla Archivos_Multimedia

CREATE TABLE Archivos_Multimedia (
    archivo_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id NUMBER NOT NULL REFERENCES Usuarios(usuario_id),
    publicacion_id NUMBER REFERENCES Publicaciones(publicacion_id),
    nombre_archivo VARCHAR2(255) NOT NULL,
    tipo_archivo VARCHAR2(50) NOT NULL,
    contenido BLOB NOT NULL, -- Contenido del archivo multimedia
    fecha_subida DATE DEFAULT SYSDATE
);


ALTER TABLE Usuarios RENAME COLUMN contraseña TO contrasena;

ALTER TABLE Publicaciones ADD imagen_publicacion BLOB;

ALTER USER C##TWITTER QUOTA UNLIMITED ON USERS;