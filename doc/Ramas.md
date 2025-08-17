# Explicación técnica

## 1. Rebase experimental sobre main, priorizando experimental

Primero se debe rebasar la rama principal desde la rama experimental utilizando `-X theirs`:

```bash
git checkout experimental
git rebase main -X theirs
```

## 2. Avanza main hasta experimental (fast-forward)

Y luego se debe desde la rama `main` o `master` escribir los siguientes comandos utilizando el parámetro `--ff-only`:

```bash
git checkout main
git merge experimental --ff-only
```

El objetivo es que si tengo:

```bash
main:
    A, B, C, D

experimental:
    A', B', C', D'
```

Puede quedar así:

```bash
main -> experimental

# Es decir:
A, B, C, D, A', B', C', D'
```