<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

interface Chain extends Resolver
{
    /**
     * Randomizes the order in with the chain is called
     */
    public function randomly(): Chain;

    /**
     * Returns all the records from each Resolver together.
     * This calls all Resolvers in the chain.
     */
    public function withAllResults(): Chain;

    /**
     * Returns the results from the first Resolver to have results.
     * This calls as many Resolvers as it needs to find the first result.
     */
    public function withFirstResults(): Chain;

    /**
     * Returns only the common results between Resolvers that had results.
     * This calls through all Resolvers in the chain
     */
    public function withConsensusResults(): Chain;
}
