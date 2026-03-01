from html.parser import HTMLParser

class MyHTMLParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.div_stack = []
        self.stack_at_346 = []

    def handle_starttag(self, tag, attrs):
        if tag == 'div':
            line, pos = self.getpos()
            cls = next((v for k, v in attrs if k == 'class'), '')
            self.div_stack.append((line, cls))

    def handle_endtag(self, tag):
        if tag == 'div':
            line, pos = self.getpos()
            if line >= 346 and not self.stack_at_346:
                self.stack_at_346 = list(self.div_stack)
                
            if self.div_stack:
                self.div_stack.pop()

parser = MyHTMLParser()
with open(r'c:\Users\messa\OneDrive\Desktop\Unimagec\Gestion-bancaire-lar\FinanFlow\resources\views\parametres\index.blade.php', 'r', encoding='utf-8') as f:
    parser.feed(f.read())

with open('out.txt', 'w', encoding='utf-8') as f:
    f.write('Open divs at line 346:\n')
    for i, (line, cls) in enumerate(parser.stack_at_346):
        f.write(f'[{i}] Line {line}: <div class="{cls}">\n')

print("Wrote to out.txt")
