from html.parser import HTMLParser

class MyHTMLParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.div_stack = []

    def handle_starttag(self, tag, attrs):
        if tag == 'div':
            line, pos = self.getpos()
            cls = next((v for k, v in attrs if k == 'class'), '')
            self.div_stack.append((line, cls))

    def handle_endtag(self, tag):
        if tag == 'div':
            line, pos = self.getpos()
            if self.div_stack:
                popped_line, popped_cls = self.div_stack.pop()
                if popped_line == 34:
                    print(f"BINGO! Line 34 flex-1 container was closed at line {line}")

parser = MyHTMLParser()
with open(r'c:\Users\messa\OneDrive\Desktop\Unimagec\Gestion-bancaire-lar\FinanFlow\resources\views\parametres\index.blade.php', 'r', encoding='utf-8') as f:
    parser.feed(f.read())
