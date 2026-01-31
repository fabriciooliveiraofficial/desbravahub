Pathfinder Club Platform — Master Architecture & Feature Document (pt-BR)
1. Visão Geral

Esta plataforma é um sistema web multi-tenant, path-based, projetado para clubes de Desbravadores no Brasil. Cada clube possui um ambiente isolado (/clube-slug/), com seus próprios usuários, dados, regras, atividades e comunicações.

O objetivo central é engajamento contínuo, progresso visível, gamificação saudável e gestão moderna de atividades, utilizando tecnologia atual, UX moderna e automações inteligentes.

2. Conceito Central de Engajamento
Pilar Principal

Aprender fazendo, provar realizando, evoluir conquistando.

O sistema gira em torno de:

Atividades progressivas

Provas reais (outdoor e indoor)

Desbloqueio de níveis

Pontuação e conquistas

Feedback constante (notificações, toasts, e-mails)

Cada Desbravador sente progresso contínuo e controle da sua jornada.

3. Arquitetura Geral
Stack

Frontend: HTML5, CSS3, JavaScript (Vanilla + componentes)

Backend: PHP 8.x (MVC)

Banco: MySQL 8.x

Hospedagem: Hostinger

SMTP: configurável por usuário

Push Notifications: Web Push

Multi-Tenant

Path-based (/clubes/{slug})

Isolamento lógico por tenant_id

Cache e versões por tenant

4. Módulos Oficiais (9)
MODULE 1 — Environment & Global Configuration

BASE_URL centralizada

Configuração de ambiente

Feature flags

Cache global

MODULE 2 — Database Schema (MySQL)

Principais entidades:

Tenants (Clubes)

Usuários

Papéis (Admin, Diretor, Desbravador)

Atividades

Tipos de Atividades

Provas

Conquistas

Pontos / XP

Eventos

Notificações

Versões

Todas as tabelas com:

tenant_id

created_at, updated_at

MODULE 3 — Authentication & Authorization

Login por clube

RBAC

Middleware por rota

Sessões isoladas

MODULE 4 — Core Activity & Progression Engine
Atividades

Indoor / Outdoor

Pré-requisitos

Escolha livre da próxima atividade

Provas

URL (YouTube, Instagram, TikTok, Facebook)

Upload de arquivos

Quiz interno validado por diretores

Progressão

XP

Níveis

Desbloqueio automático

MODULE 5 — Hybrid Notification System

Toast JS (tempo real)

Push Notifications

E-mails SMTP

Preferência por usuário

Eventos notificáveis:

Nova atividade

Prova aprovada

Conquista desbloqueada

Evento criado

MODULE 6 — Cache-First & Version Control

Cache-first strategy

Publicação de versões

Rollback

Canary release

MODULE 7 — Admin Panels (por Clube)

Gestão de usuários

Criação de atividades

Aprovação de provas

Envio de notificações

Gerenciamento de versões

MODULE 8 — Pathfinder Dashboard (Usuário)

Página exclusiva e isolada:

Perfil

Progresso visual

Agenda

Atividades disponíveis

Upload de provas

Histórico de conquistas

Integração social (URLs)

MODULE 9 — Performance, Security & Hardening

Rate limit

CSRF / XSS

Logs

Auditoria

Otimização SQL

5. Páginas Principais
Página Pública Global — Cadastro de Clubes (Home Page)

Esta é a home page principal da plataforma, acessível via domínio base (ex: https://cruzeirodosuljuveve.org/).

Objetivos

Apresentar claramente o valor da plataforma

Explicar os recursos disponíveis

Converter visitantes em novos clubes cadastrados

Estrutura da Página

Hero Section

Headline forte (missão, tecnologia, engajamento)

Subheadline explicativa

CTA principal: "Cadastrar meu Clube"

Seção de Cards de Recursos Cards visuais e modernos apresentando:

Gestão de Atividades e Provas

Gamificação (XP, níveis, conquistas)

Painel exclusivo por Desbravador

Notificações híbridas (toast, push, e-mail)

Multi-tenant (cada clube com seu ambiente)

Cache-first e performance

Segurança e isolamento de dados

Seção de Como Funciona




Cadastre seu clube




Configure diretores e atividades




Desbravadores evoluem, realizam provas e conquistam níveis

Seção de Diferenciais

Plataforma moderna

Pensada para o Brasil

Engajamento real

Escalável para vários clubes

CTA Final

Botão destacado para cadastro

Públicas (por Clube)

Landing Page do Clube

Login

Públicas

Landing Page do Clube

Login

Privadas (Usuário)

Dashboard

Atividades

Provas

Conquistas

Agenda

Privadas (Admin)

Painel Administrativo

Usuários

Atividades

Notificações

Versões

6. Regras Importantes

Isolamento total por tenant para dados operacionais (usuários, progresso, provas)

Nenhum dado pessoal é compartilhado entre clubes

Usuário vê apenas seu próprio conteúdo

Aprovação obrigatória de provas

7. Sistema Avançado de Suporte (Support System)
Visão Geral

A plataforma contará com um Sistema Avançado de Suporte, integrado nativamente, permitindo comunicação estruturada entre usuários (Desbravadores, Diretores, Admins de Clube) e o time desenvolvedor da plataforma.

O objetivo é:

Garantir estabilidade

Coletar feedback real

Corrigir bugs rapidamente

Criar sensação de cuidado, confiança e profissionalismo

Funcionalidades para Usuários (Clubes)

Disponível dentro do painel autenticado.

Abertura de Chamados

Categoria do chamado:

Bug

Dúvida

Sugestão

Solicitação de melhoria

Prioridade:

Baixa / Média / Alta

Descrição detalhada

Upload de arquivos (prints, vídeos)

Detecção automática de:

Clube (tenant)

Usuário

Página / módulo relacionado

Acompanhamento

Status do chamado:

Aberto

Em andamento

Aguardando resposta

Resolvido

Histórico completo de mensagens

Notificações automáticas a cada atualização

Funcionalidades para Desenvolvedores (Painel Global)

Painel exclusivo e isolado para o time técnico da plataforma.

Dashboard de Suporte

Lista global de chamados (todos os clubes)

Filtros por:

Clube

Categoria

Prioridade

Status

Módulo afetado

Gestão de Chamados

Responder chamados

Alterar status

Solicitar mais informações

Marcar como resolvido

Vincular chamado a:

Bug interno

Versão do sistema

Feature futura

Comunicação

Respostas internas e públicas

Notificação automática ao usuário

Histórico imutável de conversas

Integração com Versionamento

Chamados podem ser vinculados a versões

Usuário é notificado quando o problema for resolvido em nova versão

Transparência total de correções

Regras Importantes do Suporte

Isolamento por tenant (usuários veem apenas seus chamados)

Desenvolvedores veem todos os chamados

Logs obrigatórios

Nenhum chamado pode ser apagado

8. Experiência Memorável (Diferenciais)

Progressão visual clara

Feedback imediato

Sensação de jogo

Personalização

Comunicação ativa

9. Futuro

App iOS / Android (PWA-first)

Modo offline

Ranking interno

Badges animadas

10. Diretriz Final para o Agente

Priorize estabilidade, clareza, experiência humana e crescimento progressivo.

Este documento é a fonte da verdade para toda a implementação.