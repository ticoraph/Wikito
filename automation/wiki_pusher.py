#!/usr/bin/env python3
"""
Wiki Pusher - Envoie des termes avec explications à Wikito
Usage: python wiki_pusher.py "Votre terme" [--api-url URL] [--username user] [--password pass]
"""

import sys
import argparse
import json
import requests
import os
from anthropic import Anthropic

# Configuration par défaut
API_URL = os.getenv("WIKITO_API_URL", "")
USERNAME = os.getenv("WIKITO_USERNAME", "")
PASSWORD = os.getenv("WIKITO_PASSWORD", "")


def explain_term_with_claude(term: str) -> str:
    """Génère une explication 1 phrase du terme avec Claude."""
    client = Anthropic()

    message = client.messages.create(
        model="claude-haiku-5-5",
        max_tokens=150,
        messages=[
            {
                "role": "user",
                "content": f"""Explique ce terme en une seule phrase courte et claire,
                sans numéro de terme, juste l'explication directe:

                {term}"""
            }
        ]
    )

    explanation = message.content[0].text.strip()
    return explanation


def push_to_api(term: str, explanation: str, api_url: str, username: str, password: str) -> bool:
    """Envoie le terme et l'explication à Wikito."""

    payload = {
        "title": term,
        "description": explanation
    }

    headers = {
        "Content-Type": "application/json"
    }

    try:
        response = requests.post(
            api_url,
            json=payload,
            headers=headers,
            auth=(username, password),
            timeout=10
        )

        if response.status_code in [200, 201]:
            print(f"✅ Succès ! Terme '{term}' envoyé à Wikito")
            print(f"   Explication: {explanation}")
            return True
        else:
            print(f"❌ Erreur API: {response.status_code}")
            print(f"   Réponse: {response.text}")
            return False

    except Exception as e:
        print(f"❌ Erreur de connexion: {e}")
        return False


def interactive_mode():
    """Mode interactif - dialogue avec Claude pour raffiner les termes."""
    client = Anthropic()
    conversation_history = []

    print("🎓 Mode interactif Wiki")
    print("Commandes: 'add' (ajouter terme), 'quit' (quitter)")
    print("-" * 50)

    while True:
        user_input = input("\n> ").strip()

        if user_input.lower() == 'quit':
            print("Au revoir!")
            break
        elif user_input.lower() == 'add':
            term = input("Terme à ajouter: ").strip()
            if term:
                print(f"⏳ Génération de l'explication pour '{term}'...")
                explanation = explain_term_with_claude(term)
                print(f"\n📝 Explication proposée:\n   {explanation}")

                confirm = input("\nEnvoyer à Wikito ? (o/n): ").lower()
                if confirm == 'o':
                    success = push_to_api(term, explanation, API_URL, USERNAME, PASSWORD)
                    if success:
                        conversation_history.append({
                            "role": "assistant",
                            "content": f"Terme '{term}' ajouté avec succès."
                        })
        else:
            # Chat normal avec Claude
            conversation_history.append({
                "role": "user",
                "content": user_input
            })

            response = client.messages.create(
                model="claude-opus-5-5",
                max_tokens=200,
                system="Tu es un assistant pour gérer un wiki. Aide l'utilisateur à ajouter et gérer des définitions.",
                messages=conversation_history
            )

            assistant_message = response.content[0].text
            conversation_history.append({
                "role": "assistant",
                "content": assistant_message
            })

            print(f"\n🤖 Claude: {assistant_message}")


def main():
    parser = argparse.ArgumentParser(
        description="Envoyer un terme avec explication auto à Wikito"
    )
    parser.add_argument("term", nargs="?", help="Terme à expliquer")
    parser.add_argument("--api-url", default=API_URL, help="URL de l'API")
    parser.add_argument("--username", default=USERNAME, help="Nom d'utilisateur API")
    parser.add_argument("--password", default=PASSWORD, help="Mot de passe API")
    parser.add_argument("--interactive", "-i", action="store_true", help="Mode interactif")

    args = parser.parse_args()

    if not (args.api_url and args.username and args.password):
        print("❌ Configuration manquante : définir WIKITO_API_URL, WIKITO_USERNAME et "
              "WIKITO_PASSWORD (ou --api-url, --username, --password).")
        sys.exit(2)

    if args.interactive or not args.term:
        interactive_mode()
    else:
        print(f"⏳ Explication du terme: '{args.term}'")
        explanation = explain_term_with_claude(args.term)
        print(f"✏️  Explication générée:\n   {explanation}\n")

        success = push_to_api(
            args.term,
            explanation,
            args.api_url,
            args.username,
            args.password
        )

        sys.exit(0 if success else 1)


if __name__ == "__main__":
    main()
