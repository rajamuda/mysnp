from Bio import Phylo
from collections import defaultdict
import networkx, pylab, dendropy, argparse, os, json

tree_loc = ""
base_loc = ""

def distance_matrix():
	# Calculate dist matrix
	tree = dendropy.Tree.get_from_path(tree_loc, "newick")
	pdm = tree.phylogenetic_distance_matrix()

	dist_mat = defaultdict(dict)
	for i, t1 in enumerate(tree.taxon_namespace):
	    for t2 in tree.taxon_namespace[i+1:]:
	        dist_mat[t1.label][t2.label] = dist_mat[t2.label][t1.label] = pdm(t1,t2)
	        
	with open(base_loc+"/distance_matrix.json", 'wb') as outfile:
		json.dump(dist_mat, outfile)

def draw_tree():
	# Draw tree
	tree = Phylo.read(tree_loc, 'newick')

	tree.ladderize()
	Phylo.draw(tree, do_show=False)
	# pylab.axis('off')
	pylab.savefig(base_loc+"/phylo_tree.svg",format='svg', bbox_inches='tight', dpi=300)

if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.add_argument("newick_file", help="newick file location")
	args = parser.parse_args()

	tree_loc = args.newick_file
	base_loc = os.path.dirname(tree_loc)

	distance_matrix()
	draw_tree()
